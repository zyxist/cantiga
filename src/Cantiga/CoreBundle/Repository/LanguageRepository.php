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
use Cantiga\CoreBundle\Entity\Language;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Form\EntityTransformerInterface;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\Transaction;

/**
 * Description of LanguageRepository
 *
 * @author Tomasz Jędrzejewski
 */
class LanguageRepository implements EntityTransformerInterface
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
	
	public function createDataTable()
	{
		$dt = new DataTable();
		$dt->id('id', 'l.id')
			->searchableColumn('name', 'l.name')
			->searchableColumn('locale', 'l.locale');
		return $dt;
	}
	
	public function listData(DataTable $dataTable)
	{
		$qb = QueryBuilder::select()
			->field('l.id', 'id')
			->field('l.name', 'name')
			->field('l.locale', 'locale')
			->from(CoreTables::LANGUAGE_TBL, 'l');	
		
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
	
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		$data = $this->conn->fetchAssoc('SELECT * FROM `'.CoreTables::LANGUAGE_TBL.'` WHERE `id` = :id', [':id' => $id]);
		
		if(null === $data) {
			$this->transaction->requestRollback();
			throw new ItemNotFoundException('The specified language has not been found.', $id);
		}

		return Language::fromArray($data);
	}
	
	public function getLanguageCodes()
	{
		$stmt = $this->conn->query('SELECT `locale` FROM `'.CoreTables::LANGUAGE_TBL.'` ORDER BY `locale`');
		$languages = [];
		while($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$languages[] = $row[0];
		}
		$stmt->closeCursor();
		return $languages;
	}
	
	public function insert(Language $language)
	{
		$this->transaction->requestTransaction();
		return $language->insert($this->conn);
	}
	
	public function update(Language $language)
	{
		$this->transaction->requestTransaction();
		$language->update($this->conn);
	}
	
	public function remove(Language $language)
	{
		$this->transaction->requestTransaction();
		return $language->remove($this->conn);
	}
	
	public function getFormChoices()
	{
		$this->transaction->requestTransaction();
		$stmt = $this->conn->query('SELECT `id`, `name` FROM `'.CoreTables::LANGUAGE_TBL.'` ORDER BY `name`');
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
