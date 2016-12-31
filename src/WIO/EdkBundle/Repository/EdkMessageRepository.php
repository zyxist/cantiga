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
namespace WIO\EdkBundle\Repository;

use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Capabilities\InsertableRepositoryInterface;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use Exception;
use Symfony\Component\Translation\TranslatorInterface;
use WIO\EdkBundle\EdkTables;
use WIO\EdkBundle\Entity\EdkMessage;

/**
 * Manages the list of the messages from the perspective of the area, group, or entire project.
 */
class EdkMessageRepository implements InsertableRepositoryInterface
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
	 * @var TimeFormatterInterface
	 */
	private $timeFormatter;
	/**
	 * @var Project|Group|Area
	 */
	private $root;
	
	public function __construct(Connection $conn, Transaction $transaction, TimeFormatterInterface $timeFormatter)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->timeFormatter = $timeFormatter;
	}
	
	public function setRootEntity(HierarchicalInterface $root)
	{
		$this->root = $root;
	}
	
	/**
	 * @return DataTable
	 */
	public function createDataTable()
	{
		$dt = new DataTable();
		$dt->id('id', 'i.id');
		
		if (!($this->root instanceof Area)) {
			$dt->searchableColumn('areaName', 'a.name');
		}
		$dt
			->searchableColumn('subject', 'i.subject')
			->column('createdAt', 'i.createdAt')
			->column('status', 'i.status')
			->column('responder', 'u.name')
			->column('duplicate', 'i.duplicate');
		return $dt;
	}
	
	public function listData(DataTable $dataTable, TranslatorInterface $translator)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id');
		
		if (!($this->root instanceof Area)) {
			$qb->field('a.id', 'areaId');
			$qb->field('a.name', 'areaName');
			$qb->join(CoreTables::AREA_TBL, 'a', QueryClause::clause('a.id = i.areaId'));
		}
		$qb
			->field('i.subject', 'subject')
			->field('i.createdAt', 'createdAt')
			->field('i.status', 'status')
			->field('u.id', 'responderId')
			->field('u.name', 'responder')
			->field('i.duplicate', 'duplicate')
			->from(EdkTables::MESSAGE_TBL, 'i')
			->leftJoin(CoreTables::USER_TBL, 'u', QueryClause::clause('u.id = i.responderId'))
			->orderBy('i.status', 'ASC')
			->orderBy('i.createdAt', 'ASC');
		
		if ($this->root instanceof Area) {
			$qb->where(QueryClause::clause('i.`areaId` = :areaId', ':areaId', $this->root->getId()));
		} elseif ($this->root instanceof Group) {
			$qb->where(QueryClause::clause('a.`groupId` = :groupId', ':groupId', $this->root->getId()));
		} elseif ($this->root instanceof Project) {
			$qb->where(QueryClause::clause('a.`projectId` = :projectId', ':projectId', $this->root->getId()));
		}

		$qb->postprocess(function($row) use ($translator) {
			$row['statusText'] = $translator->trans(EdkMessage::statusText($row['status']), [], 'edk');
			$row['statusLabel'] = EdkMessage::statusLabel($row['status']);
			$row['createdAt'] = $this->timeFormatter->ago($row['createdAt']);
			return $row;
		});
		
		$recordsTotal = QueryBuilder::copyWithoutFields($qb)
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
	 * @return EdkMessage
	 */
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		try {
			$item = EdkMessage::fetchByRoot($this->conn, $id, $this->root);
			if(false === $item) {
				$this->transaction->requestRollback();
				throw new ItemNotFoundException('The specified message has not been found.', $id);
			}
			return $item;
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function insert($item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->insert($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function changeState(EdkMessage $item, User $currentUser, $additionalPermission, $newStatus)
	{
		$this->transaction->requestTransaction();
		try {
			$item->performTransition($currentUser, $additionalPermission, $newStatus);
			$item->changeState($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function changeDuplicateFlag(EdkMessage $item)
	{
		$this->transaction->requestTransaction();
		try {
			if ($item->getDuplicate()) {
				$item->setDuplicate(false);
			} else {
				$item->setDuplicate(true);
			}
			$item->changeState($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
}
