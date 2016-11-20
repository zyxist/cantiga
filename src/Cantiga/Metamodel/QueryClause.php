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
 * Represents a single expression in the <tt>WHERE</tt> clause. It can hold up to one
 * data binding.
 *
 * @author Tomasz Jędrzejewski
 */
class QueryClause implements QueryElement
{
	private $clause;
	private $bindingName;
	private $bindingValue;
	
	public function __construct($clause)
	{
		$this->clause = $clause;
	}
	
	public static function clause($clause, $binding = null, $value = null)
	{
		$cc = new QueryClause($clause);
		if (null !== $binding) {
			$cc->bindingName = $binding;
			$cc->bindingValue = $value;
		}
		return $cc;
	}
	
	public function registerBindings(QueryBuilder $qb)
	{
		if (null !== $this->bindingName) {
			$qb->bind($this->bindingName, $this->bindingValue);
		}
	}
	
	public function getClause()
	{
		return $this->clause;
	}

	public function build()
	{
		return '('.$this->clause.')';
	}
}
