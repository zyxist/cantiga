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
namespace Cantiga\Components\Data\Tests;
use PHPUnit\Framework\TestCase;
use Cantiga\Components\Data\Sql\Join;
use Cantiga\Components\Data\Sql\QueryClause;

class JoinTest extends TestCase
{
	public function testGeneratingInnerJoinClause()
	{
		// Given
		$join = Join::inner('foo', 'a', QueryClause::clause('a.id = b.fooId'));

		// When
		$result = $join->build();

		// Then
		$this->assertEquals('INNER JOIN `foo` AS `a` ON (a.id = b.fooId)', $result);
	}

	public function testGeneratingLeftJoinClause()
	{
		// Given
		$join = Join::left('foo', 'a', QueryClause::clause('a.id = b.fooId'));

		// When
		$result = $join->build();

		// Then
		$this->assertEquals('LEFT JOIN `foo` AS `a` ON (a.id = b.fooId)', $result);
	}

	public function testGeneratingRightJoinClause()
	{
		// Given
		$join = Join::right('foo', 'a', QueryClause::clause('a.id = b.fooId'));

		// When
		$result = $join->build();

		// Then
		$this->assertEquals('RIGHT JOIN `foo` AS `a` ON (a.id = b.fooId)', $result);
	}
}
