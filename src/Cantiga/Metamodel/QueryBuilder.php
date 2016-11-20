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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use PDO;

/**
 * Helps building SELECT queries for CRUD panels.
 *
 * @author Tomasz Jędrzejewski
 */
class QueryBuilder
{
	private $fields = array();
	private $from = null;
	private $tables = array();
	private $joins = array();
	private $where = null;
	private $orderBy = array();
	private $limit = null;
	private $offset = null;
	private $bindings = array();
	private $postprocess = null;
	
	public static function select()
	{
		return new QueryBuilder();
	}
	
	public static function copyWithoutFields(QueryBuilder $bld)
	{
		$newBuilder = clone $bld;
		$newBuilder->fields = array();
		return $newBuilder;
	}
	
	public function field($def, $alias = null)
	{
		$this->fields[] = ['def' => $def, 'alias' => $alias];
		return $this;
	}
	
	public function from($table, $alias)
	{
		$this->from = ['table' => $table, 'alias' => $alias];
		return $this;
	}
	
	public function join($table, $alias = null, QueryElement $condition = null)
	{
		if (null !== $table) {
			if ($table instanceof Join && null === $alias && null === $condition) {
				$this->joins[] = ['type' => 'INNER', 'table' => $table->getTable(), 'alias' => $table->getAlias(), 'condition' => $table->getLink()];
			} else {
				$this->joins[] = ['type' => 'INNER', 'table' => $table, 'alias' => $alias, 'condition' => $condition];
			}
		}
		return $this;
	}
	
	public function leftJoin($table, $alias, QueryElement $condition)
	{
		$this->joins[] = ['type' => 'LEFT', 'table' => $table, 'alias' => $alias, 'condition' => $condition];
		return $this;
	}
	
	public function rightJoin($table, $alias, QueryElement $condition)
	{
		$this->joins[] = ['type' => 'RIGHT', 'table' => $table, 'alias' => $alias, 'condition' => $condition];
		return $this;
	}	
	
	public function where(QueryElement $element = null)
	{
		$this->where = $element;
		return $this;
	}
	
	public function orderBy($column, $direction = 'ASC')
	{
		$this->orderBy[] = ['column' => $column, 'direction' => $direction];
		return $this;
	}
	
	/**
	 * Cleans up the already defined ORDER BY column list.
	 * 
	 * @return \Cantiga\Metamodel\QueryBuilder Fluent interface.
	 */
	public function resetOrders()
	{
		$this->orderBy = [];
		return $this;
	}
	
	public function limit($limit, $offset)
	{
		$this->limit = $limit;
		$this->offset = $offset;
		return $this;
	}
	
	public function postprocess($callback)
	{
		if (!is_callable($callback)) {
			throw new \InvalidArgumentException('QueryBuilder::postprocess() requires a callback!');
		}
		$this->postprocess = $callback;
		return $this;
	}
	
	public function bind($placeholder, $value)
	{
		$this->bindings[$placeholder] = $value;
		return $this;
	}
	
	public function getWhere()
	{
		return $this->where;
	}
	
	public function registerBindings(Statement $stmt)
	{
		if (null !== $this->where) {
			$this->where->registerBindings($this);
		}
		foreach ($this->bindings as $key => $value) {
			$stmt->bindValue($key, $value);
		}
	}
	
	public function createStatement(Connection $conn)
	{
		$stmt = $conn->prepare($this->buildQuery());
		$this->registerBindings($stmt);
		return $stmt;
	}
	
	public function fetchAll(Connection $conn)
	{
		$stmt = $this->createStatement($conn);
		$stmt->execute();
		$data = array();
		if (null !== $this->postprocess) {
			$pp = $this->postprocess;
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$data[] = $pp($row);
			}
		} else {
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$data[] = $row;
			}
		}
		$stmt->closeCursor();
		return $data;
	}
	
	public function fetchAssoc(Connection $conn)
	{
		$stmt = $this->createStatement($conn);
		$stmt->execute();
		$data = false;
		if (null !== $this->postprocess) {
			$pp = $this->postprocess;
			if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$data = $pp($row);
			}
		} else if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$data = $row;
		}
		$stmt->closeCursor();
		return $data;
	}
	
	public function fetchCell(Connection $conn)
	{
		$stmt = $this->createStatement($conn);
		$stmt->execute();
		if($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$stmt->closeCursor();
			return $row[0];
		}
		$stmt->closeCursor();
		return null;
	}
	
	public function buildQuery()
	{
		$query = 'SELECT ';
		$first = true;
		foreach ($this->fields as $field) {
			if (!$first) {
				$query .= ', ';
			}
			$query .= $field['def'];
			if (!empty($field['alias'])) {
				$query .= ' AS `'.$field['alias'].'`';
			}
			$first = false;
		}
		$query .= ' FROM `'.$this->from['table'].'` AS `'.$this->from['alias'].'` ';
		foreach ($this->joins as $join) {
			$query .= ' '.$join['type'].' JOIN `'.$join['table'].'` AS `'.$join['alias'].'` ON '.$join['condition']->build();
		}
		if (!empty($this->where)) {
			$it = $this->where->build();
			if (!empty($it)) {
				$query .= ' WHERE '.$it;
			}
		}
		if (sizeof($this->orderBy) > 0) {
			$query .= ' ORDER BY ';
			$first = true;
			foreach ($this->orderBy as $orderBy) {
				if (!$first) {
					$query .= ', ';
				}
				$query .= $orderBy['column'].' '.$orderBy['direction'];
				$first = false;
			}
		}
		if (null !== $this->limit) {
			$query .= ' LIMIT '.$this->limit.' OFFSET '.$this->offset;
		}
		return $query;
	}
}
