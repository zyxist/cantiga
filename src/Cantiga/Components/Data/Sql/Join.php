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

/**
 * Injectable JOIN clause for Query builder.
 */
class Join
{
	private $type;
	private $table;
	private $alias;
	private $link;

	/**
	 * Creates an inner join clause to the given table.
	 *
	 * @param string $table Table name
	 * @param string $alias Table alias
	 * @param QueryElementInterface $link Linking clause
	 */
	public static function inner(string $table, string $alias, QueryElementInterface $link): Join
	{
		$join = new Join();
		$join->type = 'INNER';
		$join->table = $table;
		$join->alias = $alias;
		$join->link = $link;
		return $join;
	}

	/**
	 * Creates a left inner join clause to the given table.
	 *
	 * @param string $table Table name
	 * @param string $alias Table alias
	 * @param QueryElementInterface $link Linking clause
	 */
	public static function left(string $table, string $alias, QueryElementInterface $link): Join
	{
		$join = new Join();
		$join->type = 'LEFT';
		$join->table = $table;
		$join->alias = $alias;
		$join->link = $link;
		return $join;
	}

	/**
	 * Creates a right inner join clause to the given table.
	 *
	 * @param string $table Table name
	 * @param string $alias Table alias
	 * @param QueryElementInterface $link Linking clause
	 */
	public static function right(string $table, string $alias, QueryElementInterface $link): Join
	{
		$join = new Join();
		$join->type = 'RIGHT';
		$join->table = $table;
		$join->alias = $alias;
		$join->link = $link;
		return $join;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getTable(): string
	{
		return $this->table;
	}

	public function getAlias(): string
	{
		return $this->alias;
	}

	public function getLink(): QueryElementInterface
	{
		return $this->link;
	}

	public function build(): string
	{
		return $this->type.' JOIN `'.$this->table.'` AS `'.$this->alias.'` ON '.$this->link->build();
	}
}
