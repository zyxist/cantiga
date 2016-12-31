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
declare(strict_types=1);
namespace Cantiga\CoreBundle\Repository;

use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\Components\Hierarchy\MembershipRoleResolverInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Event\AreaEvent;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\CoreBundle\Filter\AreaFilter;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Form\EntityTransformerInterface;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use Exception;
use LogicException;
use PDO;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Manages the areas in the given parent place (project or group).
 */
class AreaMgmtRepository implements EntityTransformerInterface
{
	/**
	 * @var Connection 
	 */
	private $conn;
	/**
	 * @var Transaction
	 */
	private $transaction;
	/**
	 * @var EventDispatcherInterface
	 */
	private $eventDispatcher;
	/**
	 * @var MembershipRoleResolverInterface
	 */
	private $roleResolver;
	/**
	 * Parent place
	 * @var HierarchicalInterface
	 */
	private $place;
	
	public function __construct(Connection $conn, Transaction $transaction, EventDispatcherInterface $eventDispatcher, MembershipRoleResolverInterface $roleResolver)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->eventDispatcher = $eventDispatcher;
		$this->roleResolver = $roleResolver;
	}
	
	public function setParentPlace(HierarchicalInterface $place)
	{
		$this->place = $place;
		if ($place instanceof Area) {
			throw new LogicException('Unsupported place type: Area');
		}
	}
	
	/**
	 * @return DataTable
	 */
	public function createDataTable(): DataTable
	{
		$dt = new DataTable();
		$dt->id('id', 'i.id')
			->searchableColumn('name', 'i.name')
			->column('territory', 't.name');
		if ($this->place->isRoot()) {
			$dt->searchableColumn('groupName', 'i.groupName');
		}
		$dt->searchableColumn('status', 's.id')
			->column('memberNum', 'p.memberNum')
			->column('percentCompleteness', 'i.percentCompleteness');

		
		return $dt;
	}
	
	public function listData(DataTable $dataTable, TranslatorInterface $translator): array
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.name', 'name')
			->field('i.groupName', 'groupName')
			->field('s.id', 'status')
			->field('s.name', 'statusName')
			->field('s.label', 'statusLabel')
			->field('t.name', 'territory')
			->field('p.memberNum', 'memberNum')
			->field('i.percentCompleteness', 'percentCompleteness')
			->from(CoreTables::AREA_TBL, 'i')
			->join(CoreTables::PLACE_TBL, 'p', QueryClause::clause('i.placeId = p.id'))
			->join(CoreTables::TERRITORY_TBL, 't', QueryClause::clause('i.territoryId = t.id'))
			->join(CoreTables::AREA_STATUS_TBL, 's', QueryClause::clause('i.statusId = s.id'));
		if ($this->place->isRoot()) {
			$qb->where(QueryClause::clause('i.projectId = :projectId', ':projectId', $this->place->getId()));
			if ($dataTable->hasFilter(AreaFilter::class) && $dataTable->getFilter()->isCategorySelected()) {
				$qb->join(CoreTables::GROUP_TBL, 'g', QueryClause::clause('g.id = i.groupId'));
			}
		} else {
			$qb->where(QueryClause::clause('i.groupId = :groupId', ':groupId', $this->place->getId()));
			$qb->join(CoreTables::GROUP_TBL, 'g', QueryClause::clause('g.id = i.groupId'));
		}
		
		$recordsTotal = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(i.id)', 'cnt')
			->where($dataTable->buildCountingCondition($qb->getWhere()))
			->fetchCell($this->conn);
		$recordsFiltered = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(i.id)', 'cnt')
			->where($dataTable->buildFetchingCondition($qb->getWhere()))
			->fetchCell($this->conn);

		$qb->postprocess(function($row) use($translator) {
			$row['statusName'] = $translator->trans($row['statusName'], [], 'statuses');
			$row['percentCompleteness'] .= '%';
			return $row;
		});
		$dataTable->processQuery($qb);
		return $dataTable->createAnswer(
			$recordsTotal,
			$recordsFiltered,
			$qb->where($dataTable->buildFetchingCondition($qb->getWhere()))->fetchAll($this->conn)
		);
	}
	
	public function getItem($id): Area
	{
		$this->transaction->requestTransaction();
		try {
			$item = Area::fetchByPlace($this->conn, $id, $this->place);
			if (false === $item) {
				$this->transaction->requestRollback();
				throw new ItemNotFoundException('The specified area has not been found.', $id);
			}
			return $item;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function findMembers(Area $area): array
	{
		$this->transaction->requestTransaction();
		try {
			return $area->getPlace()->findMembers($this->conn, $this->roleResolver);
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function insert(Area $item): int
	{
		$this->transaction->requestTransaction();
		try {
			$id = $item->insert($this->conn);
			$this->eventDispatcher->dispatch(CantigaEvents::AREA_CREATED, new AreaEvent($item));
			return (int) $id;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function update(Area $item)
	{
		$this->transaction->requestTransaction();
		try {
			$item->update($this->conn);
			$this->eventDispatcher->dispatch(CantigaEvents::AREA_UPDATED, new AreaEvent($item));
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function remove(Area $item)
	{
		$this->transaction->requestTransaction();
		try {
			$item->remove($this->conn);
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function getFormChoices(): array
	{
		if ($this->place->getTypeName() == 'Group') {
			$field = 'groupId';
		} elseif ($this->place->getTypeName() == 'Project') {
			$field = 'projectId';
		}
		
		$this->transaction->requestTransaction();
		$stmt = $this->conn->prepare('SELECT `id`, `name` FROM `'.CoreTables::AREA_TBL.'` WHERE `'.$field.'` = :'.$field.' ORDER BY `name`');
		$stmt->bindValue(':'.$field, $this->place->getId());
		$stmt->execute();
		$result = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$result[$row['name']] = $row['id'];
		}
		$stmt->closeCursor();
		return $result;
	}

	public function transformToEntity($key)
	{
		return $this->getItem($key);
	}

	public function transformToKey($entity)
	{
		return $entity->getId();
	}
}
