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
namespace Cantiga\MilestoneBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\Metamodel\Transaction;
use Cantiga\MilestoneBundle\Entity\MilestoneStatusRule;
use Cantiga\MilestoneBundle\MilestoneTables;
use Doctrine\DBAL\Connection;
use Symfony\Component\Translation\TranslatorInterface;

class ProjectMilestoneStatusRuleRepository
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
	/**
	 * @var TimeFormatterInterface
	 */
	private $timeFormatter;
	
	public function __construct(Connection $conn, Transaction $transaction, TimeFormatterInterface $timeFormatter)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->timeFormatter = $timeFormatter;
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
			->column('newStatus', 's1.name')
			->column('prevStatus', 's2.name')
			->column('activationOrder', 'i.activationOrder')
			->column('lastUpdatedAt', 'i.lastUpdatedAt');
		return $dt;
	}
	
	public function listData(DataTable $dataTable, TranslatorInterface $translator)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.name', 'name')
			->field('s1.name', 'newStatus')
			->field('s1.label', 'newStatusLabel')
			->field('s2.name', 'prevStatus')
			->field('s2.label', 'prevStatusLabel')
			->field('i.activationOrder', 'activationOrder')
			->field('i.lastUpdatedAt', 'lastUpdatedAt')
			->from(MilestoneTables::MILESTONE_STATUS_RULE_TBL, 'i')
			->join(CoreTables::AREA_STATUS_TBL, 's1', QueryClause::clause('i.newStatusId = s1.id'))
			->join(CoreTables::AREA_STATUS_TBL, 's2', QueryClause::clause('i.prevStatusId = s2.id'))
			->where(QueryClause::clause('i.`projectId` = :projectId', ':projectId', $this->project->getId()));

		$qb->postprocess(function($row) use($translator) {
			$row['lastUpdatedAt'] = $this->timeFormatter->ago($row['lastUpdatedAt']);
			return $row;
		});
		
		$recordsTotal = QueryBuilder::select()
			->field('COUNT(id)', 'cnt')
			->from(MilestoneTables::MILESTONE_STATUS_RULE_TBL, 'i')
			->where($dataTable->buildCountingCondition(QueryClause::clause('i.`projectId` = :projectId', ':projectId', $this->project->getId())))
			->fetchCell($this->conn);
		$recordsFiltered = QueryBuilder::select()
			->field('COUNT(id)', 'cnt')
			->from(MilestoneTables::MILESTONE_STATUS_RULE_TBL, 'i')
			->where($dataTable->buildFetchingCondition(QueryClause::clause('i.`projectId` = :projectId', ':projectId', $this->project->getId())))
			->fetchCell($this->conn);
		$dataTable->processQuery($qb);
		return $dataTable->createAnswer(
			$recordsTotal,
			$recordsFiltered,
			$qb->where($dataTable->buildFetchingCondition($qb->getWhere()))->fetchAll($this->conn)
		);
	}
	
	/**
	 * @return MilestoneStatusRule
	 */
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		try {
			$item = MilestoneStatusRule::fetchByProject($this->conn, $id, $this->project);

			if(false === $item) {
				$this->transaction->requestRollback();
				throw new ItemNotFoundException('The specified item has not been found.', $id);
			}
			$item->fetchMilestoneSummary($this->conn);
			return $item;
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function insert(MilestoneStatusRule $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->insert($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function update(MilestoneStatusRule $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->update($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function remove(MilestoneStatusRule $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->remove($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
}