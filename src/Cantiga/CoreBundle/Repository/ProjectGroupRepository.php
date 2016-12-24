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
namespace Cantiga\CoreBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\CoreBundle\Event\GroupEvent;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Form\EntityTransformerInterface;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProjectGroupRepository implements EntityTransformerInterface
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
	 * Active project
	 * @var Project
	 */
	private $project;
	
	public function __construct(Connection $conn, Transaction $transaction, EventDispatcherInterface $eventDispatcher)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->eventDispatcher = $eventDispatcher;
	}
	
	public function setProject(Project $project)
	{
		$this->project = $project;
	}
	
	/**
	 * @return DataTable
	 */
	public function createDataTable()
	{
		$dt = new DataTable();
		$dt->id('id', 'i.id')
			->searchableColumn('name', 'i.name')
			->searchableColumn('categoryName', 'c.name')
			->column('memberNum', 'p.memberNum')
			->column('areaNum', 'i.areaNum');
		return $dt;
	}
	
	public function listData(DataTable $dataTable)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.name', 'name')
			->field('c.name', 'categoryName')
			->field('p.memberNum', 'memberNum')
			->field('i.areaNum', 'areaNum')
			->from(CoreTables::GROUP_TBL, 'i')
			->join(CoreTables::PLACE_TBL, 'p', QueryClause::clause('p.id = i.placeId'))
			->leftJoin(CoreTables::GROUP_CATEGORY_TBL, 'c', QueryClause::clause('c.id = i.categoryId'))
			->where(QueryClause::clause('i.projectId = :projectId', ':projectId', $this->project->getId()));	
		
		$countingQuery = QueryBuilder::select()
			->from(CoreTables::GROUP_TBL, 'i')
			->where(QueryClause::clause('i.projectId = :projectId', ':projectId', $this->project->getId()));	
		
		$recordsTotal = QueryBuilder::copyWithoutFields($countingQuery)
			->field('COUNT(i.id)', 'cnt')
			->where($dataTable->buildCountingCondition($qb->getWhere()))
			->fetchCell($this->conn);
		$recordsFiltered = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(i.id)', 'cnt')
			->where($dataTable->buildFetchingCondition($qb->getWhere()))
			->fetchCell($this->conn);

		$dataTable->processQuery($qb);
		return $dataTable->createAnswer(
			$recordsTotal,
			$recordsFiltered,
			$qb->where($dataTable->buildFetchingCondition($qb->getWhere()))->fetchAll($this->conn)
		);
	}
	
	/**
	 * @return Group
	 */
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		try {
			$item = Group::fetchByProject($this->conn, $id, $this->project);
			if(false === $item) {
				$this->transaction->requestRollback();
				throw new ItemNotFoundException('The specified item has not been found.', $id);
			}
			return $item;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function findMembers(Group $group)
	{
		$items = $this->conn->fetchAll('SELECT u.name, u.avatar, p.location, p.telephone, p.publicMail, p.privShowTelephone, p.privShowPublicMail, m.note '
			. 'FROM `'.CoreTables::USER_TBL.'` u '
			. 'INNER JOIN `'.CoreTables::USER_PROFILE_TBL.'` p ON p.`userId` = u.`id` '
			. 'INNER JOIN `'.CoreTables::GROUP_MEMBER_TBL.'` m ON m.`userId` = u.`id` '
			. 'WHERE m.`groupId` = :groupId ORDER BY m.`role` DESC, u.`name`', [':groupId' => $group->getId()]);
		
		foreach ($items as &$item) {
			$item['publicMail'] = (User::evaluateUserPrivacy($item['privShowPublicMail'], $this->project) ? $item['publicMail'] : '');
			$item['telephone'] = (User::evaluateUserPrivacy($item['privShowTelephone'], $this->project) ? $item['telephone'] : '');
		}
		return $items;
	}
	
	public function findGroupAreas(Group $group)
	{
		$this->transaction->requestTransaction();
		try {
			return $this->conn->fetchAll('SELECT a.`id`, a.`name`, p.`memberNum`, s.`id` AS `statusId`, s.`name` AS `statusName`, s.`label` AS `statusLabel`, t.`id` AS `territoryId`, t.`name` AS `territoryName` '
				. 'FROM `'.CoreTables::AREA_TBL.'` a '
				. 'INNER JOIN `'.CoreTables::PLACE_TBL.'` p ON p.`id` = a.`placeId` '
				. 'INNER JOIN `'.CoreTables::AREA_STATUS_TBL.'` s ON s.`id` = a.`statusId` '
				. 'INNER JOIN `'.CoreTables::TERRITORY_TBL.'` t ON t.`id` = a.`territoryId` '
				. 'WHERE a.`groupId` = :id ORDER BY a.`name`', [':id' => $group->getId()]);
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function insert(Group $item)
	{
		$this->transaction->requestTransaction();
		try {
			$result = $item->insert($this->conn);
			$this->eventDispatcher->dispatch(CantigaEvents::GROUP_CREATED, new GroupEvent($item));
			return $result;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function update(Group $item)
	{
		$this->transaction->requestTransaction();
		try {
			$item->update($this->conn);
			$this->eventDispatcher->dispatch(CantigaEvents::GROUP_UPDATED, new GroupEvent($item));
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function remove(Group $item)
	{
		$this->transaction->requestTransaction();
		try {
			$item->remove($this->conn);
			$this->eventDispatcher->dispatch(CantigaEvents::GROUP_REMOVED, new GroupEvent($item));
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function getFormChoices()
	{
		$this->transaction->requestTransaction();
		$stmt = $this->conn->prepare('SELECT `id`, `name` FROM `'.CoreTables::GROUP_TBL.'` WHERE `projectId` = :projectId ORDER BY `name`');
		$stmt->bindValue(':projectId', $this->project->getId());
		$stmt->execute();
		$result = array();
		$result['---'] = null;
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$result[$row['name']] = $row['id'];
		}
		$stmt->closeCursor();
		return $result;
	}

	public function transformToEntity($key)
	{
		if (empty($key)) {
			return null;
		}
		return $this->getItem($key);
	}

	public function transformToKey($entity)
	{
		if (null !== $entity) {
			return $entity->getId();
		}
		return 0;
	}
}