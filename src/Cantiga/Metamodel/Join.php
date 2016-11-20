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
 * Injectable JOIN clause for Query builder.
 *
 * @author Tomasz Jędrzejewski
 */
class Join
{
	private $table;
	private $alias;
	private $link;
	
	public static function create($table, $alias, QueryElement $link)
	{
		$join = new Join();
		$join->table = $table;
		$join->alias = $alias;
		$join->link = $link;
		return $join;
	}
	
	public function getTable()
	{
		return $this->table;
	}

	public function getAlias()
	{
		return $this->alias;
	}

	/**
	 * @return QueryElement
	 */
	public function getLink()
	{
		return $this->link;
	}
}
