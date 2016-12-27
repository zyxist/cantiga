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

use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\AreaStatus;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Form\EntityTransformerInterface;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use PDO;

class ProjectAreaStatusRepository implements EntityTransformerInterface
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
	 * @var Project
	 */
	private $project;
	
	public function __construct(Connection $conn, Transaction $transaction)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
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
			->column('label', 'i.label')
			->column('isDefault', 'i.isDefault')
			->column('areaNum', 'i.areaNum');
		return $dt;
	}
	
	public function listData(DataTable $dataTable)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.name', 'name')
			->field('i.label', 'label')
			->field('i.isDefault', 'isDefault')
			->field('i.areaNum', 'areaNum')
			->from(CoreTables::AREA_STATUS_TBL, 'i');
		$where = QueryClause::clause('i.`projectId` = :projectId', ':projectId', $this->project->getId());
		
		$recordsTotal = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(id)', 'cnt')
			->where($dataTable->buildCountingCondition($where))
			->fetchCell($this->conn);
		$recordsFiltered = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(id)', 'cnt')
			->where($dataTable->buildFetchingCondition($where))
			->fetchCell($this->conn);

		$dataTable->processQuery($qb);
		return $dataTable->createAnswer(
			$recordsTotal,
			$recordsFiltered,
			$qb->where($dataTable->buildFetchingCondition($where))->fetchAll($this->conn)
		);
	}
	
	/**
	 * @return AreaStatus
	 */
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		$item = AreaStatus::fetchByProject($this->conn, $id, $this->project);
		
		if(false === $item) {
			$this->transaction->requestRollback();
			throw new ItemNotFoundException('The specified item has not been found.', $id);
		}
		return $item;
	}
	
	public function insert(AreaStatus $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->insert($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function update(AreaStatus $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->update($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function remove(AreaStatus $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->remove($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function importFrom(HierarchicalInterface $source, HierarchicalInterface $destination)
	{
		$this->transaction->requestTransaction();
		try {
			$sourceStatus = $this->conn->fetchAll('SELECT `name`, `label`, `isDefault` FROM `'.CoreTables::AREA_STATUS_TBL.'` WHERE `projectId` = :sourceProjectId FOR UPDATE', [':sourceProjectId' => $source->getId()]);
			$destinationStatus = $this->conn->fetchAll('SELECT `name` FROM `'.CoreTables::AREA_STATUS_TBL.'` WHERE `projectId` = :dstProjectId FOR UPDATE', [':dstProjectId' => $destination->getId()]);
			$set = [];
			foreach ($destinationStatus as $row) {
				$set[$row['name']] = true;
			}
			foreach ($sourceStatus as $status) {
				if (!isset($set[$status['name']])) {
					$item = new AreaStatus();
					$item->setProject($destination);
					$item->setName($status['name']);
					$item->setLabel($status['label']);
					$item->setIsDefault((bool) $status['isDefault']);
					$item->insert($this->conn);
				}
			}
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}

	public function getFormChoices(Project $project = null)
	{
		if (null === $project) {
			$project = $this->project;
		}
		
		$this->transaction->requestTransaction();
		$stmt = $this->conn->prepare('SELECT `id`, `name` FROM `'.CoreTables::AREA_STATUS_TBL.'` WHERE `projectId` = :projectId ORDER BY `name`');
		$stmt->bindValue(':projectId', $project->getId());
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