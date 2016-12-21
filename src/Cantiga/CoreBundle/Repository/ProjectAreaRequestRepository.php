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

use Cantiga\Components\Hierarchy\MembershipRoleResolverInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\AreaRequest;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\CoreBundle\Event\AreaEvent;
use Cantiga\CoreBundle\Event\AreaRequestApprovedEvent;
use Cantiga\CoreBundle\Event\AreaRequestEvent;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\Metamodel\Transaction;
use Cantiga\UserBundle\Entity\ContactData;
use Doctrine\DBAL\Connection;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Manages the area requests from the perspective of a project.
 */
class ProjectAreaRequestRepository
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
	 * Active project
	 * @var Project
	 */
	private $project;
	/**
	 * @var EventDispatcherInterface 
	 */
	private $eventDispatcher;
	/**
	 * @var MembershipRoleResolverInterface
	 */
	private $resolver;
	
	public function __construct(Connection $conn, Transaction $transaction, TimeFormatterInterface $timeFormatter, EventDispatcherInterface $eventDispatcher, MembershipRoleResolverInterface $resolver)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->timeFormatter = $timeFormatter;
		$this->eventDispatcher = $eventDispatcher;
		$this->resolver = $resolver;
	}
	
	public function setActiveProject(Project $project)
	{
		$this->project = $project;
	}
	
	public function createDataTable(): DataTable
	{
		$dt = new DataTable();
		$dt->id('id', 'i.id')
			->searchableColumn('name', 'i.name')
			->column('territory', 't.name')
			->column('reportedBy', 'u.name')
			->column('status', 'i.status')
			->column('commentNum', 'i.commentNum');
		return $dt;
	}
	
	public function listData(TranslatorInterface $trans, DataTable $dataTable)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.name', 'name')
			->field('t.name', 'territory')
			->field('u.name', 'reportedBy')
			->field('i.status', 'status')
			->field('i.commentNum', 'commentNum')
			->from(CoreTables::AREA_REQUEST_TBL, 'i')
			->join(CoreTables::USER_TBL, 'u', QueryClause::clause('u.`id` = i.`requestorId`'))
			->join(CoreTables::TERRITORY_TBL, 't', QueryClause::clause('t.`id` = i.`territoryId`'))
			->where(QueryClause::clause('i.projectId = :projectId', ':projectId', $this->project->getId()));	
		
		$countingQuery = QueryBuilder::select()
			->from(CoreTables::AREA_REQUEST_TBL, 'i')
			->where(QueryClause::clause('i.projectId = :projectId', ':projectId', $this->project->getId()));
		
		$recordsTotal = QueryBuilder::copyWithoutFields($countingQuery)
			->field('COUNT(id)', 'cnt')
			->where($dataTable->buildCountingCondition($qb->getWhere()))
			->fetchCell($this->conn);
		$recordsFiltered = QueryBuilder::copyWithoutFields($countingQuery)
			->field('COUNT(id)', 'cnt')
			->where($dataTable->buildFetchingCondition($qb->getWhere()))
			->fetchCell($this->conn);

		$qb->postprocess(function($row) use($trans) {
			$row['statusLabel'] = AreaRequest::statusLabel($row['status']);
			$row['statusText'] = $trans->trans(AreaRequest::statusText($row['status']), [], 'statuses');
			$row['removable'] = ($row['status'] == 0 || $row['status'] == 1);
			return $row;
		});
		$dataTable->processQuery($qb);
		return $dataTable->createAnswer(
			$recordsTotal,
			$recordsFiltered,
			$qb->where($dataTable->buildFetchingCondition($qb->getWhere()))->fetchAll($this->conn)
		);
	}
	
	public function getItem($id): AreaRequest
	{
		$this->transaction->requestTransaction();
		try {
			$item = AreaRequest::fetchByProject($this->conn, $id, $this->project);
			if(null === $item) {
				throw new ItemNotFoundException('The specified item has not been found.', $id);
			}
			$item->setContactData(ContactData::findContactData($this->conn, $this->project, $item->getRequestor()));
			return $item;
		} catch (Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function getFeedback(AreaRequest $item)
	{
		$this->transaction->requestTransaction();
		try {
			return [
				'status' => 1,
				'messageNum' => $item->getCommentNum(),
				'messages' => $item->getFeedback($this->conn, $this->timeFormatter)
			];
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function getRecentFeedbackActivity($count)
	{
		$this->transaction->requestTransaction();
		try {
			$items = $this->conn->fetchAll('SELECT r.`id` AS `requestId`, r.name AS `requestName`, u.`name` AS `username`, u.`avatar`, c.`createdAt`, c.`message` '
				. 'FROM `'.CoreTables::AREA_REQUEST_COMMENT_TBL.'` c '
				. 'INNER JOIN `'.CoreTables::AREA_REQUEST_TBL.'` r ON r.`id` = c.`requestId` '
				. 'INNER JOIN `'.CoreTables::USER_TBL.'` u ON u.`id` = c.`userId` '
				. 'WHERE r.`projectId` = :projectId '
				. 'ORDER BY c.`createdAt` DESC LIMIT '.$count, [':projectId' => $this->project->getId()]);
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
	
	public function getRecentRequests($count)
	{
		$this->transaction->requestTransaction();
		try {
			$items = $this->conn->fetchAll('SELECT r.`id`, r.`name`, u.`name` AS `username`, u.`avatar`, r.`status`, r.`createdAt` '
				. 'FROM `'.CoreTables::AREA_REQUEST_TBL.'` r '
				. 'INNER JOIN `'.CoreTables::USER_TBL.'` u ON u.`id` = r.`requestorId` '
				. 'WHERE r.`projectId` = :projectId '
				. 'ORDER BY r.`createdAt` DESC LIMIT '.$count, [':projectId' => $this->project->getId()]);
			foreach ($items as &$item) {
				$item['statusText'] = AreaRequest::statusText($item['status']);
				$item['statusLabel'] = AreaRequest::statusLabel($item['status']);
			}
			return $items;
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function update(AreaRequest $item)
	{
		$this->transaction->requestTransaction();
		$item->update($this->conn);
	}
	
	public function remove(AreaRequest $item)
	{
		$this->transaction->requestTransaction();
		try {
			if (!$item->remove($this->conn)) {
				throw new ModelException('Cannot remove the specified area request.');
			}
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function startVerification(AreaRequest $item, User $verifier)
	{
		$this->transaction->requestTransaction();
		try {
			if(!$item->startVerification($this->conn, $verifier)) {
				throw new ModelException('Cannot start the verification for this request.');
			}
			$this->eventDispatcher->dispatch(CantigaEvents::AREA_REQUEST_VERIFICATION, new AreaRequestEvent($item));
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function approve(AreaRequest $item)
	{
		$this->transaction->requestTransaction();
		try {
			$area = $item->approve($this->conn, $this->resolver);
			if(false === $area) {
				throw new ModelException('Cannot revoke this this request.');
			}
			$this->eventDispatcher->dispatch(CantigaEvents::AREA_REQUEST_APPROVED, new AreaRequestApprovedEvent($item, $area));
			$this->eventDispatcher->dispatch(CantigaEvents::AREA_CREATED, new AreaEvent($area));
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function revoke(AreaRequest $item)
	{
		$this->transaction->requestTransaction();
		try {
			if(!$item->revoke($this->conn)) {
				throw new ModelException('Cannot revoke this this request.');
			}
			$this->eventDispatcher->dispatch(CantigaEvents::AREA_REQUEST_REVOKED, new AreaRequestEvent($item));
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
}
