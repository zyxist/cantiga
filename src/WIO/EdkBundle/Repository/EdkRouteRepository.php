<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\Capabilities\MembershipEntityInterface;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\FileRepositoryInterface;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\Metamodel\Transaction;
use Cantiga\MilestoneBundle\Entity\NewMilestoneStatus;
use Cantiga\MilestoneBundle\Event\ActivationEvent;
use Cantiga\MilestoneBundle\MilestoneEvents;
use Doctrine\DBAL\Connection;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use WIO\EdkBundle\EdkTables;
use WIO\EdkBundle\Entity\EdkRoute;

/**
 * @author Tomasz Jędrzejewski
 */
class EdkRouteRepository
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
	 * @var TimeFormatterInterface
	 */
	private $timeFormatter;
	/**
	 * @var FileRepositoryInterface
	 */
	private $fileRepository;
	/**
	 * @var Project|Group|Area
	 */
	private $root;
	
	public function __construct(Connection $conn, Transaction $transaction, EventDispatcherInterface $eventDispatcher, TimeFormatterInterface $timeFormatter, FileRepositoryInterface $fileRepository)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->eventDispatcher = $eventDispatcher;
		$this->timeFormatter = $timeFormatter;
		$this->fileRepository = $fileRepository;
	}
	
	public function setRootEntity(MembershipEntityInterface $root)
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
			->searchableColumn('name', 'i.name')
			->searchableColumn('routeFrom', 'i.routeFrom')
			->searchableColumn('routeTo', 'i.routeTo')
			->column('routeLength', 'i.routeLength')
			->column('updatedAt', 'i.updatedAt')
			->column('approved', 'i.approved')
			->column('commentNum', 'i.commentNum');
		return $dt;
	}
	
	public function listData(DataTable $dataTable)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id');
		
		if (!($this->root instanceof Area)) {
			$qb->field('a.id', 'areaId');
			$qb->field('a.name', 'areaName');
			$qb->join(CoreTables::AREA_TBL, 'a', QueryClause::clause('a.id = i.areaId'));
		}
		$qb
			->field('i.name', 'name')
			->field('i.routeFrom', 'routeFrom')
			->field('i.routeTo', 'routeTo')
			->field('i.routeLength', 'routeLength')
			->field('i.updatedAt', 'updatedAt')
			->field('i.approved', 'approved')
			->field('i.commentNum', 'commentNum')
			->from(EdkTables::ROUTE_TBL, 'i');
		
		if ($this->root instanceof Area) {
			$qb->where(QueryClause::clause('i.`areaId` = :areaId', ':areaId', $this->root->getId()));
		} elseif ($this->root instanceof Group) {
			$qb->where(QueryClause::clause('a.`groupId` = :groupId', ':groupId', $this->root->getId()));
		} elseif ($this->root instanceof Project) {
			$qb->where(QueryClause::clause('a.`projectId` = :projectId', ':projectId', $this->root->getId()));
		}
			

		$qb->postprocess(function($row) {
			$row['routeLength'] .= ' km';
			$row['updatedAtText'] = $this->timeFormatter->ago($row['updatedAt']);
			$row['removable'] = !$row['approved'];
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
	 * @return EdkRoute
	 */
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		try {
			$item = EdkRoute::fetchByRoot($this->conn, $id, $this->root);
			if(false === $item) {
				$this->transaction->requestRollback();
				throw new ItemNotFoundException('The specified item has not been found.', $id);
			}
			return $item;
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function getComments(EdkRoute $item)
	{
		$this->transaction->requestTransaction();
		try {
			return [
				'status' => 1,
				'messageNum' => $item->getCommentNum(),
				'messages' => $item->getComments($this->conn, $this->timeFormatter)
			];
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	/**
	 * @return EdkRoute
	 */
	public function getItemBySlug($slug)
	{
		$this->transaction->requestTransaction();
		try {
			$item = EdkRoute::fetchBySlug($this->conn, $slug);
			if(false === $item) {
				$this->transaction->requestRollback();
				throw new ItemNotFoundException('The specified item has not been found.', $id);
			}
			return $item;
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function insert(EdkRoute $item)
	{
		$this->transaction->requestTransaction();
		try {
			if ($this->root instanceof Area) {
				$item->setArea($this->root);
			}
			$item->storeFiles($this->fileRepository);
			return $item->insert($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function update(EdkRoute $item)
	{
		$this->transaction->requestTransaction();
		try {
			$item->updateFiles($this->fileRepository);
			return $item->update($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function remove(EdkRoute $item)
	{
		$this->transaction->requestTransaction();
		try {
			$item->cleanupFiles($this->fileRepository);
			return $item->remove($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function getRecentCommentActivity($count)
	{
		$this->transaction->requestTransaction();
		try {
			$items = $this->conn->fetchAll('SELECT r.`id` AS `routeId`, r.name AS `routeName`, a.`name` AS `areaName`, u.`name` AS `username`, u.`avatar`, c.`createdAt`, c.`message` '
				. 'FROM `'.EdkTables::ROUTE_COMMENT_TBL.'` c '
				. 'INNER JOIN `'.EdkTables::ROUTE_TBL.'` r ON r.`id` = c.`routeId` '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON r.`areaId` = a.`id` '
				. 'INNER JOIN `'.CoreTables::USER_TBL.'` u ON u.`id` = c.`userId` '
				. $this->createWhereClause()
				. 'ORDER BY c.`createdAt` DESC LIMIT '.$count, [':itemId' => $this->root->getId()]);
			foreach ($items as &$item) {
				if (strlen($item['message']) > 60) {
					$item['truncatedContent'] = substr($item['message'], 0, 60);
					if (ord($item['truncatedContent']{59}) > 127) {
						$item['truncatedContent'] = substr($item['message'], 0, 59);
					}
					$item['truncatedContent'] .= '...';
				} else {
					$item['truncatedContent'] = $item['message'];
				}
			}
			return $items;
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function getRecentlyChangedRoutes($count)
	{
		$this->transaction->requestTransaction();
		try {
			$items = $this->conn->fetchAll('SELECT r.`id`, r.`name`, a.`name` AS `areaName`, r.`updatedAt` '
				. 'FROM `'.EdkTables::ROUTE_TBL.'` r '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON r.`areaId` = a.`id` '
				. $this->createWhereClause()
				. 'ORDER BY r.`updatedAt` DESC LIMIT '.$count, [':itemId' => $this->root->getId()]);
			foreach ($items as &$item) {
				$item['updatedAt'] = $this->timeFormatter->ago($item['updatedAt']);
			}
			return $items;
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function countRoutes()
	{
		$this->transaction->requestTransaction();
		try {
			return $this->conn->fetchColumn('SELECT COUNT(r.`id`) '
				. 'FROM `'.EdkTables::ROUTE_TBL.'` r '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON r.`areaId` = a.`id` '
				. $this->createWhereClause(), [':itemId' => $this->root->getId()]);
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function approve(EdkRoute $item)
	{
		$this->transaction->requestTransaction();
		try {
			if(!$item->approve($this->conn)) {
				throw new ModelException('Cannot approve this this route.');
			}
			$this->eventDispatcher->dispatch(MilestoneEvents::ACTIVATION_EVENT, new ActivationEvent(
				$item->getArea()->getProject(),
				$item->getArea()->getEntity(),
				'route.approved',
				$this->getActivationFunc($item)
			));
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function revoke(EdkRoute $item)
	{
		$this->transaction->requestTransaction();
		try {
			if(!$item->revoke($this->conn)) {
				throw new ModelException('Cannot revoke this this route.');
			}
			$this->eventDispatcher->dispatch(MilestoneEvents::ACTIVATION_EVENT, new ActivationEvent(
				$item->getArea()->getProject(),
				$item->getArea()->getEntity(),
				'route.approved',
				$this->getActivationFunc($item)
			));
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	private function getActivationFunc(EdkRoute $route)
	{
		$conn = $this->conn;
		return function() use($conn, $route) {
			$count = $conn->fetchColumn('SELECT COUNT(`id`) FROM `'.EdkTables::ROUTE_TBL.'` WHERE `approved` = 1 AND `routeType` = '.EdkRoute::TYPE_FULL.' AND `areaId` = :areaId', [':areaId' => $route->getArea()->getId()]);
			return NewMilestoneStatus::create($count > 0);
		};
	}
	
	private function createWhereClause()
	{
		if ($this->root instanceof Area) {
			return 'WHERE r.`areaId` = :itemId ';				
		} elseif ($this->root instanceof Group) {
			return 'WHERE a.`groupId` = :itemId ';
		} elseif ($this->root instanceof Project) {
			return 'WHERE a.`projectId` = :itemId ';
		}
	}
}
