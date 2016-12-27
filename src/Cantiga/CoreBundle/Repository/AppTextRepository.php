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
use Cantiga\CoreBundle\Entity\AppText;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Form\EntityTransformerInterface;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\HttpFoundation\Request;

class AppTextRepository implements EntityTransformerInterface
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
			->searchableColumn('place', 'i.place')
			->searchableColumn('title', 'i.title')
			->column('locale', 'i.locale');
		return $dt;
	}
	
	public function listData(DataTable $dataTable)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.place', 'place')
			->field('i.title', 'title')
			->field('i.locale', 'locale')
			->from(CoreTables::APP_TEXT_TBL, 'i');
		if (null === $this->project) {
			$where = QueryClause::clause('i.`projectId` IS NULL');
		} else {
			$where = QueryClause::clause('i.`projectId` = :projectId', ':projectId', $this->project->getId());
		}
		$qb->where($where);
		
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
		$item = AppText::fetchById($this->conn, $id, $this->project);
		if(null === $item) {
			$this->transaction->requestRollback();
			throw new ItemNotFoundException('The specified item has not been found.', $id);
		}
		return $item;
	}
	
	/**
	 * Fetches the text by the place name, the currently set locale, and optionally - the project. The method never
	 * fails - if the text does not exist, it returns a dummy entity that indicates a missing text.
	 * 
	 * @param string $place Name of the place, where the text is shown
	 * @param \Cantiga\CoreBundle\Repository\Request $request Request is used to obtain the locale
	 * @param Project $project If project is specified, project-specific texts are prioritized over general ones.
	 * @return AppText
	 */
	public function getText($place, Request $request, Project $project = null)
	{
		$locale = $request->getLocale();
		$this->transaction->requestTransaction();
		$item = AppText::fetchByLocation($this->conn, $place, $locale, $project);
		if(false === $item) {
			$item = new AppText();
			$item->setTitle('No title');
			$item->setContent('No content');
			$item->setLocale($locale);
			$item->setPlace($place);
			$item->markEmpty();
		}
		return $item;
	}
	
	/**
	 * Fetches the text by the place name, the currently set locale, and optionally - the project. The method returns
	 * <strong>false</strong>, if no text has been found.
	 * 
	 * @param string $place Name of the place, where the text is shown
	 * @param \Cantiga\CoreBundle\Repository\Request $request Request is used to obtain the locale
	 * @param Project $project If project is specified, project-specific texts are prioritized over general ones.
	 * @return AppText|false
	 */
	public function getTextOrFalse($place, Request $request, Project $project = null)
	{
		$locale = $request->getLocale();
		$this->transaction->requestTransaction();
		return AppText::fetchByLocation($this->conn, $place, $locale, $project);
	}
	
	public function insert(AppText $item)
	{
		$this->transaction->requestTransaction();
		try {
			if (null !== $this->project) {
				$item->setProject($this->project);
			}
			return $item->insert($this->conn);
		} catch(\Exception $exception) {
			$this->transaction->requestRollback();
		}
	}
	
	public function update(AppText $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->update($this->conn);
		} catch(\Exception $exception) {
			$this->transaction->requestRollback();
		}
	}
	
	public function remove(AppText $item)
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
		$stmt = $this->conn->query('SELECT `id`, `name` FROM `'.CoreTables::APP_TEXT_TBL.'` ORDER BY `name`');
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