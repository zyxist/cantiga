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
use Cantiga\CoreBundle\Entity\AreaRequest;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Event\AreaRequestEvent;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\Form\EntityTransformerInterface;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserAreaRequestRepository implements EntityTransformerInterface
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
	 * @var TokenStorageInterface
	 */
	private $tokenStorage;
	/**
	 * @var TimeFormatterInterface
	 */
	private $timeFormatter;
	
	public function __construct(Connection $conn, Transaction $transaction, EventDispatcherInterface $eventDispatcher, TimeFormatterInterface $timeFormatter, TokenStorageInterface $tokenStorage)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->eventDispatcher = $eventDispatcher;
		$this->tokenStorage = $tokenStorage;
		$this->timeFormatter = $timeFormatter;
	}
	
	public function findUserRequests()
	{
		$stmt = $this->conn->prepare('SELECT r.`id`, p.`name` AS `projectName`, r.`name`, r.`createdAt`, r.`lastUpdatedAt`, r.`status`, r.`commentNum` '
			. 'FROM `'.CoreTables::AREA_REQUEST_TBL.'` r '
			. 'INNER JOIN `'.CoreTables::PROJECT_TBL.'` p ON p.`id` = r.`projectId` '
			. 'WHERE r.`requestorId` = :id AND p.`archived` = 0 ORDER BY p.`id` DESC, r.`status`');
		$stmt->bindValue(':id', $this->tokenStorage->getToken()->getUser()->getId());
		$stmt->execute();
		
		$results = array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$row['statusLabel'] = AreaRequest::statusLabel($row['status']);
			$row['statusText'] = AreaRequest::statusText($row['status']);
			
			$results[] = $row;
		}
		$stmt->closeCursor();
		return $results;
	}
	
	/**
	 * @return DataTable
	 */
	public function createDataTable()
	{
		$dt = new DataTable();
		$dt->id('id', 'i.id')
			->searchableColumn('name', 'i.name');
		return $dt;
	}
	
	public function listData(DataTable $dataTable)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.name', 'name')
			->from(CoreTables::AREA_REQUEST_TBL, 'i');	
		
		$recordsTotal = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(id)', 'cnt')
			->where($dataTable->buildCountingCondition($qb->getWhere()))
			->fetchCell($this->conn);
		$recordsFiltered = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(id)', 'cnt')
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
	 * @return AreaRequest
	 */
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		$item = AreaRequest::fetchByRequestor($this->conn, $id, $this->tokenStorage->getToken()->getUser());
		if(null === $item) {
			$this->transaction->requestRollback();
			throw new ItemNotFoundException('The specified item has not been found.', $id);
		}
		return $item;
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
	
	public function update(AreaRequest $item)
	{
		$this->transaction->requestTransaction();
		try {
			$item->update($this->conn);
			$this->eventDispatcher->dispatch(CantigaEvents::AREA_REQUEST_UPDATED, new AreaRequestEvent($item));
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	/**
	 * Fetches a project, where the area registration is currently possible.
	 * 
	 * @param int $id Project ID
	 * @return Project
	 * @throws Cantiga\CoreBundle\Repository\Exception
	 * @throws ItemNotFoundException
	 */
	public function getAvailableProject($id)
	{
		$this->transaction->requestTransaction();
		try {
			$project = Project::fetchAvailableForRegistration($this->conn, $id);
			if (false === $project) {
				throw new ItemNotFoundException('The specified project is not available for the area registration.');
			}
			return $project;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function getAvailableProjects()
	{
		$stmt = $this->conn->query('SELECT `id`, `name`, `description` FROM `'.CoreTables::PROJECT_TBL.'` WHERE `archived` = 0 AND `areasAllowed` = 1 AND `areaRegistrationAllowed` = 1 ORDER BY `name`');
		$result = array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$result[] = $row;
		}
		$stmt->closeCursor();
		return $result;
	}
	
	/**
	 * Sends a request for the creation of the new area.
	 * 
	 * @param AreaRequest $request
	 */
	public function insert(AreaRequest $request)
	{
		$request->setRequestor($this->tokenStorage->getToken()->getUser());
		$this->transaction->requestTransaction();
		try {
			$id = $request->insert($this->conn);
			$this->eventDispatcher->dispatch(CantigaEvents::AREA_REQUEST_CREATED, new AreaRequestEvent($request));
			return $id;
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
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

	public function getFormChoices()
	{
		$this->transaction->requestTransaction();
		$stmt = $this->conn->query('SELECT `id`, `name` FROM `'.CoreTables::AREA_REQUEST_TBL.'` ORDER BY `name`');
		$result = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$result[$row['id']] = $row['name'];
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