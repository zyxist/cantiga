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
namespace Cantiga\ExportBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\ExportBundle\ExportTables;
use Cantiga\ExportBundle\Entity\DataExport;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use Exception;
use Symfony\Component\Translation\TranslatorInterface;

class ExportRepository
{
	/**
	 * @var Connection 
	 */
	private $conn;
	/**
	 * @var Transaction
	 */
	private $transaction;
	
	public function __construct(Connection $conn, Transaction $transaction)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
	}
	
	/**
	 * @return DataTable
	 */
	public function createDataTable()
	{
		$dt = new DataTable();
		$dt->id('id', 'i.id')
			->searchableColumn('name', 'i.name')
			->column('project', 'p.name')
			->column('active', 'i.active');
		return $dt;
	}
	
	public function listData(DataTable $dataTable)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.name', 'name')
			->field('p.name', 'project')
			->field('i.active', 'active')
			->from(ExportTables::DATA_EXPORT_TBL, 'i')
			->join(CoreTables::PROJECT_TBL, 'p', QueryClause::clause('i.projectId = p.id'));
		
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
	 * @return DataExport
	 */
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		$item = DataExport::fetchById($this->conn, $id);
		
		if(false === $item) {
			$this->transaction->requestRollback();
			throw new ItemNotFoundException('The specified item has not been found.', $id);
		}
		return $item;
	}
	
	public function insert(DataExport $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->insert($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function update(DataExport $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->update($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function remove(DataExport $item)
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