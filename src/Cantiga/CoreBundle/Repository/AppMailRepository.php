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

use Doctrine\DBAL\Connection;
use PDO;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\AppMail;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Form\EntityTransformerInterface;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\Transaction;

class AppMailRepository implements EntityTransformerInterface
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
			->searchableColumn('place', 'i.place')
			->searchableColumn('subject', 'i.subject')
			->column('locale', 'i.locale');
		return $dt;
	}
	
	public function listData(DataTable $dataTable)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.place', 'place')
			->field('i.subject', 'subject')
			->field('i.locale', 'locale')
			->from(CoreTables::MAIL_TBL, 'i');	
		
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
	 * @return AppText
	 */
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		$item = AppMail::fetchById($this->conn, $id);
		if(null === $item) {
			$this->transaction->requestRollback();
			throw new ItemNotFoundException('The specified item has not been found.', $id);
		}
		return $item;
	}
	
	public function insert(AppMail $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->insert($this->conn);
		} catch(\Exception $exception) {
			$this->transaction->requestRollback();
		}
	}
	
	public function update(AppMail $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->update($this->conn);
		} catch(\Exception $exception) {
			$this->transaction->requestRollback();
		}
	}
	
	public function remove(AppMail $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->remove($this->conn);
		} catch(\Exception $exception) {
			$this->transaction->requestRollback();
		}
	}
	
	public function getFormChoices()
	{
		$this->transaction->requestTransaction();
		$stmt = $this->conn->query('SELECT `id`, `name` FROM `'.CoreTables::MAIL_TBL.'` ORDER BY `name`');
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