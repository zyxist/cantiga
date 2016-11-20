<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga contributors.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace Cantiga\Metamodel;

use Symfony\Component\HttpFoundation\Request;

/**
 * Manages the server side processing of dynamic, JS-based data table. You specify all the columns used by your
 * repository, and the class automatically handles request parameter parsing, generating proper SQL query clauses
 * for sorting, pagination and searching. The class co-operates with Twig helper methods which produce the necessary
 * JavaScript code for columns directly from this definition.
 *
 * @author Tomasz Jędrzejewski
 */
class DataTable
{
	const TYPE_ID = 0;
	const TYPE_STD = 1;
	const TYPE_SEARCHABLE = 2;
	
	// -- configuration
	private $columns = array();
	
	// --- data retrieved from request
	private $draw;
	private $start;
	private $length;
	private $searchString = null;
	private $orders = array();
	private $filter;
	
	/**
	 * Defines the column with the row identifier.
	 * 
	 * @param string $name Data set column name
	 * @param string $dbColumn Column reference in SQL query.
	 * @return Cantiga\Metamodel\DataTable
	 */
	public function id($name, $dbColumn)
	{
		$this->columns[] = ['type' => self::TYPE_ID, 'name' => $name, 'db' => $dbColumn];
		return $this;
	}
	
	/**
	 * Defines a regular, sortable column.
	 * 
	 * @param string $name Data set column name
	 * @param string $dbColumn Column reference in SQL query.
	 * @return Cantiga\Metamodel\DataTable
	 */
	public function column($name, $dbColumn)
	{
		$this->columns[] = ['type' => self::TYPE_STD, 'name' => $name, 'db' => $dbColumn];
		return $this;
	}
	
	/**
	 * Defines the sortable column, which is also searchable. If the user types something in
	 * the search field, all the searchable fields are checked - one of them must contain the
	 * given phrase.
	 * 
	 * @param string $name Data set column name
	 * @param string $dbColumn Column reference in SQL query.
	 * @return Cantiga\Metamodel\DataTable
	 */
	public function searchableColumn($name, $dbColumn)
	{
		$this->columns[] = ['type' => self::TYPE_SEARCHABLE, 'name' => $name, 'db' => $dbColumn];
		return $this;
	}
	
	/**
	 * Installs a filter. Usually the filter is a form that the user can customize. The filter allows
	 * greater flexibility in choosing, what we want to see.
	 * 
	 * @param \Cantiga\Metamodel\DataFilterInterface $filter
	 * @return Cantiga\Metamodel\DataTable
	 */
	public function filter(DataFilterInterface $filter)
	{
		$this->filter = $filter;
		return $this;
	}
	
	/**
	 * Checks if any filter is installed in this data table.
	 * 
	 * @param string $filterType Checks that the filter is also an implementation of the specified class/interface
	 * @return boolean
	 */
	public function hasFilter($filterType = null)
	{
		if (null === $this->filter) {
			return false;
		}
		if (null !== $filterType) {
			return is_a($this->filter, $filterType);
		}
		return true;
	}
	
	/**
	 * @return DataFilterInterface
	 */
	public function getFilter()
	{
		return $this->filter;
	}

	/**
	 * Processes the request and parses the variables produced by JS front-end for data tables.
	 * 
	 * @param Request $request
	 */
	public function process(Request $request)
	{
		$this->draw = (int) $request->get('draw', 1);
		$search = $request->get('search');
		
		if (!empty($search['value'])) {
			$this->searchString = str_replace('%', '', $search['value']);
		}
		$order = $request->get('order', []);
		if (is_array($order)) {
			foreach ($order as $i => $cfg) {
				$colId = (int) $cfg['column'];
				if (isset($this->columns[$colId])) {
					$this->orders[] = ['dbName' => $this->columns[$colId]['db'], 'direction' => $cfg['dir'] === 'asc' ? 'ASC' : 'DESC'];
				}
			}
		}
		
		$this->start = (int) $request->get('start', 1);
		$this->length = (int) $request->get('length', 25);
		if ($this->length < 1 || $this->length > 100) {
			$this->length = 25;
		}
	}
	
	/**
	 * Returns the number of columns.
	 * 
	 * @return int 
	 */
	public function columnCount()
	{
		return sizeof($this->columns);
	}
	
	/**
	 * Returns the definitions of all columns. The returned value is an array of enumerated rows
	 * with three cells: <tt>type</tt>, <tt>key</tt> and <tt>db</tt>.
	 * 
	 * @return array
	 */
	public function getColumnDefinitions()
	{
		return $this->columns;
	}
	
	/**
	 * The method shall be used in the repositories to produce the part of the <tt>WHERE</tt>
	 * clause that will be used for fetching the data from the database.
	 * 
	 * @param Cantiga\Metamodel\QueryElement $custom
	 * @return QueryOperator
	 */
	public function buildFetchingCondition(QueryElement $custom = null)
	{
		$op = QueryOperator::op(' AND ');
		$op
			->expr($custom)
			->expr($this->buildFilterClause())
			->expr($this->buildSearchClause());
		return $op;
	}
	
	/**
	 * The method shall be used in the repositories to produce the part of the <tt>WHERE</tt>
	 * clause that will be used for counting the number of available rows.
	 * 
	 * @param Cantiga\Metamodel\QueryElement $custom
	 * @return QueryOperator
	 */
	public function buildCountingCondition(QueryElement $custom = null)
	{
		$op = QueryOperator::op(' AND ');
		return $op->expr($custom);
	}
	
	private function buildFilterClause()
	{
		if (null !== $this->filter) {
			return $this->filter->createFilterClause();
		}
		return null;
	}
	
	private function buildSearchClause()
	{
		$bIdx = 0;
		$op = QueryOperator::op(' OR ');
		if (!empty($this->searchString)) {
			foreach ($this->columns as $column) {
				if ($column['type'] == self::TYPE_SEARCHABLE) {
					$op->expr(QueryClause::clause($column['db'].' LIKE :bIdx'.$bIdx, ':bIdx'.$bIdx, '%'.$this->searchString.'%'));
					$bIdx++;
				}
			}
		}
		
		if($bIdx > 0) {
			return $op;
		}
		return null;
	}
	
	/**
	 * Adds the <tt>ORDER BY</tt> and <tt>LIMIT</tt> clauses to the given SQL query, and returns
	 * that query for convenience.
	 * 
	 * @param \Cantiga\Metamodel\QueryBuilder $qb
	 * @return \Cantiga\Metamodel\QueryBuilder
	 */
	public function processQuery(QueryBuilder $qb)
	{
		$qb->limit($this->length, $this->start);
		if (sizeof($this->orders) > 0) {
			$qb->resetOrders();
			foreach ($this->orders as $order) {
				$qb->orderBy($order['dbName'], $order['direction']);
			}
		}
		return $qb;
	}
	
	/**
	 * Constructs the final result set understood by the JavaScript front end.
	 * 
	 * @param int $total The total number of rows
	 * @param int $filtered The number of rows after applying the search filter.
	 * @param array $data Rows displayed on the current page.
	 * @return array
	 */
	public function createAnswer($total, $filtered, $data)
	{
		return ['draw' => $this->draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data];
	}
}
