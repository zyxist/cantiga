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
use Cantiga\Components\Data\Sql\QueryBuilder;
use Cantiga\Components\Data\Sql\QueryClause;
use Cantiga\Components\Data\Sql\Join;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;

class QueryBuilderTest extends TestCase
{
	public function testSimpleQuery()
	{
		// Given
		$qb = QueryBuilder::select()
			->field('id')
			->from('foo')
			->where(QueryClause::clause('name = \'Bar\''));
		// When
		$sql = $qb->buildQuery();

		// Then
		$this->assertEquals('SELECT id FROM `foo`  WHERE (name = \'Bar\')', $sql);
	}

	public function testQueryWithJoin()
	{
		// Given
		$qb = QueryBuilder::select()
			->field('id')
			->from('foo')
			->join(Join::inner('bar', 'b', QueryClause::clause('b.id = foo.bId')))
			->where(QueryClause::clause('name = \'Bar\''));
		// When
		$sql = $qb->buildQuery();

		// Then
		$this->assertEquals('SELECT id FROM `foo`  INNER JOIN `bar` AS `b` ON (b.id = foo.bId) WHERE (name = \'Bar\')', $sql);
	}

	public function testQueryWithOrder()
	{
		// Given
		$qb = QueryBuilder::select()
			->field('id')
			->from('foo')
			->where(QueryClause::clause('name = \'Bar\''))
			->orderBy('xyz', QueryBuilder::DESC);
		// When
		$sql = $qb->buildQuery();

		// Then
		$this->assertEquals('SELECT id FROM `foo`  WHERE (name = \'Bar\') ORDER BY xyz DESC', $sql);
	}

	public function testQueryWithLimit()
	{
		// Given
		$qb = QueryBuilder::select()
			->field('id')
			->from('foo')
			->where(QueryClause::clause('name = \'Bar\''))
			->limit(2, 5);
		// When
		$sql = $qb->buildQuery();

		// Then
		$this->assertEquals('SELECT id FROM `foo`  WHERE (name = \'Bar\') LIMIT 2 OFFSET 5', $sql);
	}

	public function testQueryWithFieldAliases()
	{
		// Given
		$qb = QueryBuilder::select()
			->field('id', 'my_id')
			->field('joe', 'my_joe')
			->from('foo', 'f')
			->where(QueryClause::clause('f.name = \'Bar\''));
		// When
		$sql = $qb->buildQuery();

		// Then
		$this->assertEquals('SELECT id AS `my_id`, joe AS `my_joe` FROM `foo` AS `f`  WHERE (f.name = \'Bar\')', $sql);
	}

	public function testFullQuery()
	{
		// Given
		$qb = QueryBuilder::select()
			->field('id', 'my_id')
			->field('joe', 'my_joe')
			->field('moo')
			->from('foo', 'f')
			->join(Join::inner('bar', 'b', QueryClause::clause('b.id = foo.bId')))
			->join(Join::left('loo', 'l', QueryClause::clause('l.id = foo.lId')))
			->where(QueryClause::clause('f.name = \'Bar\''))
			->orderBy('id')
			->limit(4, 7);
		// When
		$sql = $qb->buildQuery();

		// Then
		$this->assertEquals('SELECT id AS `my_id`, joe AS `my_joe`, moo FROM `foo` AS `f`  INNER JOIN `bar` AS `b` ON (b.id = foo.bId) '
			. 'LEFT JOIN `loo` AS `l` ON (l.id = foo.lId) WHERE (f.name = \'Bar\') ORDER BY id ASC LIMIT 4 OFFSET 7', $sql);
	}

	public function testFetchAllWithoutPostprocessing()
	{
		// Given
		$qb = $this->createSimpleQueryBuilder();
		[$conn, $stmt] = $this->createMockConnection($qb->buildQuery());
		$stmt->expects($this->exactly(3))
			->method('fetch')
			->with(\PDO::FETCH_ASSOC)
			->will($this->onConsecutiveCalls(
				['id' => 1, 'name' => 'foo'],
				['id' => 2, 'name' => 'bar'],
				false
			));

		// When
		$result = $qb->fetchAll($conn);

		// Then
		$this->assertEquals([
			['id' => 1, 'name' => 'foo'],
			['id' => 2, 'name' => 'bar'],
		], $result);
	}

	public function testFetchAllWithPostprocessing()
	{
		// Given
		$qb = $this->createSimpleQueryBuilder();
		$qb->postprocess(function(array $row): array {
			$row['xyz'] = 'modified';
			return $row;
		});
		[$conn, $stmt] = $this->createMockConnection($qb->buildQuery());
		$stmt->expects($this->exactly(3))
			->method('fetch')
			->with(\PDO::FETCH_ASSOC)
			->will($this->onConsecutiveCalls(
				['id' => 1, 'name' => 'foo'],
				['id' => 2, 'name' => 'bar'],
				false
			));

		// When
		$result = $qb->fetchAll($conn);

		// Then
		$this->assertEquals([
			['id' => 1, 'name' => 'foo', 'xyz' => 'modified'],
			['id' => 2, 'name' => 'bar', 'xyz' => 'modified'],
		], $result);
	}

	public function testFetchAssocWithoutPostprocessing()
	{
		// Given
		$qb = $this->createSimpleQueryBuilder();
		[$conn, $stmt] = $this->createMockConnection($qb->buildQuery());
		$stmt->expects($this->once())
			->method('fetch')
			->with(\PDO::FETCH_ASSOC)
			->will($this->returnValue(['id' => 1, 'name' => 'foo']));

		// When
		$result = $qb->fetchAssoc($conn);

		// Then
		$this->assertEquals(['id' => 1, 'name' => 'foo'], $result);
	}

	public function testFetchAssocWithPostprocessing()
	{
		// Given
		$qb = $this->createSimpleQueryBuilder();
		$qb->postprocess(function(array $row): array {
			$row['xyz'] = 'modified';
			return $row;
		});
		[$conn, $stmt] = $this->createMockConnection($qb->buildQuery());
		$stmt->expects($this->once())
			->method('fetch')
			->with(\PDO::FETCH_ASSOC)
			->will($this->returnValue(['id' => 1, 'name' => 'foo']));

		// When
		$result = $qb->fetchAssoc($conn);

		// Then
		$this->assertEquals(['id' => 1, 'name' => 'foo', 'xyz' => 'modified'], $result);
	}

	public function testFetchColumnWithoutPostprocessing()
	{
		// Given
		$qb = $this->createSimpleQueryBuilder();
		[$conn, $stmt] = $this->createMockConnection($qb->buildQuery());
		$stmt->expects($this->exactly(3))
			->method('fetch')
			->with(\PDO::FETCH_NUM)
			->will($this->onConsecutiveCalls([0 => 'a'], [0 => 'b'], false));

		// When
		$result = $qb->fetchColumn($conn);

		// Then
		$this->assertEquals(['a', 'b'], $result);
	}

	public function testFetchColumnWithPostprocessing()
	{
		// Given
		$qb = $this->createSimpleQueryBuilder();
		$qb->postprocess(function($value): string {
			return $value.'a';
		});
		[$conn, $stmt] = $this->createMockConnection($qb->buildQuery());
		$stmt->expects($this->exactly(3))
			->method('fetch')
			->with(\PDO::FETCH_NUM)
			->will($this->onConsecutiveCalls([0 => 'a'], [0 => 'b'], false));

		// When
		$result = $qb->fetchColumn($conn);

		// Then
		$this->assertEquals(['aa', 'ba'], $result);
	}

	public function testFetchCell()
	{
		// Given
		$qb = $this->createSimpleQueryBuilder();
		[$conn, $stmt] = $this->createMockConnection($qb->buildQuery());
		$stmt->expects($this->once())
			->method('fetch')
			->with(\PDO::FETCH_NUM)
			->will($this->returnValue([0 => 42]));

		// When
		$result = $qb->fetchCell($conn);

		// Then
		$this->assertEquals(42, $result);
	}

	private function createMockConnection(string $expectedQuery): array
	{
		$conn = $this->getMockBuilder(Connection::class)
			->disableOriginalConstructor()
			->getMock();
		$stmt = $this->getMockBuilder(Statement::class)
			->disableOriginalConstructor()
			->getMock();
		$conn->expects($this->exactly(1))
			->method('prepare')
			->with($expectedQuery)
			->will($this->returnValue($stmt));

		$stmt->expects($this->exactly(1))
			->method('execute');
		$stmt->expects($this->exactly(1))
			->method('closeCursor');

		return [$conn, $stmt];
	}

	private function createSimpleQueryBuilder(): QueryBuilder
	{
		return QueryBuilder::select()
			->field('id')
			->from('foo')
			->where(QueryClause::clause('name = \'Bar\''));
	}
}
