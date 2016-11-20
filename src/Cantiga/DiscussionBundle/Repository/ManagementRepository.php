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
namespace Cantiga\DiscussionBundle\Repository;

use Cantiga\CoreBundle\Entity\Project;
use Cantiga\DiscussionBundle\DiscussionTables;
use Cantiga\DiscussionBundle\Entity\Channel;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use Symfony\Component\Translation\TranslatorInterface;

class ManagementRepository
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
			->column('discussionGrouping', 'i.discussionGrouping')
			->column('enabled', 'i.enabled');
		return $dt;
	}
	
	public function listData(DataTable $dataTable, TranslatorInterface $translator)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.name', 'name')
			->field('i.discussionGrouping', 'discussionGrouping')
			->field('i.enabled', 'enabled')
			->from(DiscussionTables::DISCUSSION_CHANNEL_TBL, 'i')
			->where(QueryClause::clause('i.`projectId` = :projectId', ':projectId', $this->project->getId()));

		$qb->postprocess(function($row) use($translator) {
			$row['discussionGroupingText'] = $translator->trans(Channel::discussionGroupingText($row['discussionGrouping']), [], 'discussion');
			return $row;
		});
		
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
	 * @return Channel
	 */
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		$item = Channel::fetchByProject($this->conn, $id, $this->project);
		
		if(false === $item) {
			$this->transaction->requestRollback();
			throw new ItemNotFoundException('The specified item has not been found.', $id);
		}
		return $item;
	}
	
	public function insert(Channel $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->insert($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function update(Channel $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->update($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function remove(Channel $item)
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
