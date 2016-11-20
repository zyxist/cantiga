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
 * Represents a connection of two sub-expressions with a logical operator
 * in the <tt>WHERE</tt> or <tt>JOIN</tt> clauses.
 *
 * @author Tomasz Jędrzejewski
 */
class QueryOperator implements QueryElement
{
	private $operator;
	private $expressions = array();
	
	private function __construct($operator)
	{
		$this->operator = $operator;
	}
	
	public static function op($operator)
	{
		return new QueryOperator($operator);
	}
	
	public function expr(QueryElement $element = null)
	{
		if (null !== $element) {
			$this->expressions[] = $element;
		}
		return $this;
	}
	
	public function registerBindings(QueryBuilder $qb)
	{
		foreach ($this->expressions as $expr) {
			$expr->registerBindings($qb);
		}
	}

	public function build()
	{
		if (sizeof($this->expressions) == 0) {
			return null;
		}
		if (sizeof($this->expressions) == 1) {
			return '('.$this->expressions[0]->build().')';
		}
		$subItems = array();
		foreach($this->expressions as $expr) {
			$result = $expr->build();
			if (null !== $result) {
				$subItems[] = $result;
			}
		}
		return '('.implode(' '.$this->operator.' ', $subItems).')';
	}
}
