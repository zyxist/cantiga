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
 * along with Cantiga; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
declare(strict_types=1);
namespace Cantiga\Components\Data\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use PDO;

/**
 * A builder for <tt>SELECT</tt> SQL queries. It can be used both for generating the query
 * text, and actually executing it with returning the results.
 */
class QueryBuilder
{
	const ASC = 'ASC';
	const DESC = 'DESC';

	private $fields = [];
	private $from = null;
	private $tables = [];
	private $joins = [];
	private $where = null;
	private $orderBy = [];
	private $limit = null;
	private $offset = null;
	private $bindings = [];
	private $postprocess = null;

	public static function select(): QueryBuilder
	{
		return new QueryBuilder();
	}

	public static function copyWithoutFields(QueryBuilder $bld)
	{
		$newBuilder = clone $bld;
		$newBuilder->fields = array();
		return $newBuilder;
	}

	public function field(string $def, ?string $alias = null): self
	{
		$this->fields[] = ['def' => $def, 'alias' => $alias];
		return $this;
	}

	public function from(string $table, ?string $alias = null): self
	{
		$this->from = ['table' => $table, 'alias' => $alias];
		return $this;
	}

	public function join(Join $join): self
	{
		$this->joins[] = $join;
		return $this;
	}

	public function where(?QueryElementInterface $element): self
	{
		$this->where = $element;
		return $this;
	}

	public function orderBy(string $column, string $direction = self::ASC): self
	{
		$this->orderBy[] = ['column' => $column, 'direction' => $direction];
		return $this;
	}

	public function limit(int $limit, int $offset): self
	{
		$this->limit = $limit;
		$this->offset = $offset;
		return $this;
	}

	public function bind(string $placeholder, $value): self
	{
		$this->bindings[$placeholder] = $value;
		return $this;
	}

	public function getWhere(): ?QueryElementInterface
	{
		return $this->where;
	}

	/**
	 * Cleans up the already defined ORDER BY column list.
	 *
	 * @return QueryBuilder Fluent interface.
	 */
	public function resetOrders(): self
	{
		$this->orderBy = [];
		return $this;
	}

	/**
	 * Installs a post-processing callback for fetchXXX() methods. The callback accepts the
	 * returned row and returns its modified form.
	 *
	 * @param  callable $callback Post-processing callback for rows
	 * @return self               Fluent interface
	 */
	public function postprocess(callable $callback): self
	{
		$this->postprocess = $callback;
		return $this;
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

	public function createStatement(Connection $conn): Statement
	{
		$stmt = $conn->prepare($this->buildQuery());
		$this->registerBindings($stmt);
		return $stmt;
	}

	/**
	 * Executes the query and returns all the rows as an array of associative arrays.
	 * If the query returns no data, an empty array is returned. If the postprocess
	 * closure is specified, it is called for each returned row and shall return its
	 * modified form.
	 *
	 * @param  Connection $conn Database connection
	 * @return array            Array of associative arrays representing all the returned rows.
	 */
	public function fetchAll(Connection $conn): array
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

	/**
	 * Executes the query and returns the first row as an array. If the row is not available, <strong>false</strong>
	 * is returned. If the postprocess closure is specified, it is called for the returned row and shall return its
	 * modified form.
	 *
	 * @param  Connection $conn Database connection
	 * @return array|false      Returned row or FALSE if the query returns no data.
	 */
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

	/**
	 * Executes a query that returns multiple rows with a single column. The column
	 * values are returned as an array. If the query returns more columns, remaining
	 * ones are ignored. If the postprocess closure is specified, it is called for
	 * each returned value and shall return the modified value, which is then stored
	 * in the resulting array.
	 *
	 * @param Connection $conn Database connection
	 * @return array Array of column values
	 */
	public function fetchColumn(Connection $conn): array
	{
		$stmt = $this->createStatement($conn);
		$stmt->execute();
		$data = array();
		if (null !== $this->postprocess) {
			$pp = $this->postprocess;
			while($row = $stmt->fetch(PDO::FETCH_NUM)) {
				$data[] = $pp($row[0]);
			}
		} else {
			while($row = $stmt->fetch(PDO::FETCH_NUM)) {
				$data[] = $row[0];
			}
		}
		$stmt->closeCursor();
		return $data;
	}

	/**
	 * Executes a query that returns a single row with just one column, and returns the
	 * value of this column. Postprocessing closure is not executed in this method.
	 *
	 * @param  Connection $conn Database connection
	 * @return mixed            Value of the single column in the row.
	 */
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

	public function buildQuery(): string
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
		$query .= $this->buildFromClause();
		foreach ($this->joins as $join) {
			$query .= ' '.$join->build();
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

	private function buildFromClause(): string
	{
		if (!empty($this->from['alias'])) {
			return ' FROM `'.$this->from['table'].'` AS `'.$this->from['alias'].'` ';
		} else {
			return ' FROM `'.$this->from['table'].'` ';
		}
	}
}
