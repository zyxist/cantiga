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

/**
 * Used for reducing the number of JOIN clauses in your queries. Modifies the results of the previous query
 * by appending additional cells to each row. The new cells are retrieved by a separate query and matched by
 * certain key.
 * 
 * <ol>
 *  <li>Pass the original query statement in the constructor,</li>
 *  <li>Add one or more enhancing queries with {@link addEnhancer()}</li>
 *  <li>Enhance the result set from the original query with {@link enhance()}</li>
 * </ol>
 *
 * @author Tomasz Jędrzejewski
 */
class ResultEnhancer
{
	private $stmt;
	private $enhancers = array();
	private $extractors = array();
	
	public function __construct(\Doctrine\DBAL\Statement $stmt)
	{
		$this->stmt = $stmt;
	}
	
	/**
	 * Specifies the new enhancing query. If the enhancing query contains <tt>WHERE</tt> clause,
	 * the enhancer adds <tt>AND matchingColumn IN (...)</tt> clause to it. Otherwise a completely
	 * new <tt>WHERE</tt> statement is attached.
	 * 
	 * @param string $keyColumn Key column from the original result set
	 * @param string $matchingColumn Matching column in the enhancing query
	 * @param string $matchingAlias Alias of the matching column in the enhancing query
	 * @param string $prefix Prefix added to the names of enhancing cells
	 * @param \Cantiga\Metamodel\QueryBuilder $query Enhancing query (may contain WHERE clause).
	 * @return \Cantiga\Metamodel\ResultEnhancer
	 */
	public function addEnhancer($keyColumn, $matchingColumn, $matchingAlias, $prefix, QueryBuilder $query)
	{
		$this->enhancers[] = ['key' => $keyColumn, 'matchingColumn' => $matchingColumn, 'matchingAlias' => $matchingAlias, 'prefix' => $prefix, 'query' => $query];
		$this->extractors[$keyColumn] = array();
		return $this;
	}
	
	/**
	 * Runs all the enhancing queries and combines their results with the result from the original
	 * query.
	 * 
	 * @param \Cantiga\Metamodel\Connection $conn
	 * @return array
	 */
	public function enhance(Connection $conn)
	{
		$original = array();
		while ($row = $this->stmt->fetch(PDO::FETCH_ASSOC)) {
			$original[] = $row;
		}
		$stmt->closeCursor();
		
		foreach ($this->extractors as $extractor) {
			$this->extract($conn, $original, $extractor);
		}
		return $original;
	}
	
	private function extract(Connection $conn, array &$original, array &$extractor)
	{
		$keyset = [];
		foreach ($original as &$item) {
			if (isset($item[$extractor['key']])) {
				$keyset[] = $item[$extractor['key']];
			}
		}
		if (sizeof($keyset) > 0) {
			$where = $extractor['query']->getWhere();
			if (null === $where) {
				$extractor['query']->where(new QueryClause($extractor['matchingColumn'].' IN ('.implode(',', $keyset).')'));
			} else {
				$extractor['query']->where(QueryOperator::op('AND')
					->expr(new QueryClause($extractor['matchingColumn'].' IN ('.implode(',', $keyset).')'))
					->expr($where)
				);
			}
			
			$stmt = $extractor['query']->createStatement($conn);
			$retrieved = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$retrieved[$row[$extractor['matchingAlias']]] = $row;
			}
			$stmt->closeCursor();
		}
		
		foreach ($original as &$item) {
			if (isset($item[$extractor['key']]) && isset($retrieved[$item[$extractor['key']]])) {
				foreach ($retrieved[$item[$extractor['key']]] as $key => $value) {
					$item[$extractor['prefix'].ucfirst($key)] = $value;
				}
			}
		}
	}
}
