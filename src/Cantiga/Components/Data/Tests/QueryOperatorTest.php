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
use Cantiga\Components\Data\Sql\QueryOperator;
use Cantiga\Components\Data\Sql\QueryClause;

class QueryOperatorTest extends TestCase
{
	public function testSingleClause()
	{
		// Given
		$join = QueryOperator::op('AND')
			->expr(QueryClause::clause('foo'));

		// When
		$result = $join->build();

		// Then
		$this->assertEquals('((foo))', $result);
	}

	public function testMultipleClauses()
	{
		// Given
		$join = QueryOperator::op('AND')
			->expr(QueryClause::clause('foo'))
			->expr(QueryClause::clause('bar'));

		// When
		$result = $join->build();

		// Then
		$this->assertEquals('((foo) AND (bar))', $result);
	}

	public function testMultipleClausesWithNull()
	{
		// Given
		$join = QueryOperator::op('AND')
			->expr(QueryClause::clause('foo'))
			->expr(null)
			->expr(QueryClause::clause('bar'));

		// When
		$result = $join->build();

		// Then
		$this->assertEquals('((foo) AND (bar))', $result);
	}
}
