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
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\CoreBundle\Event\ProjectArchivizedEvent;
use Cantiga\CoreBundle\Event\ProjectCreatedEvent;
use Cantiga\CoreBundle\Settings\ProjectSettings;
use Cantiga\CoreBundle\Settings\SettingsStorageInterface;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Form\EntityTransformerInterface;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProjectRepository implements EntityTransformerInterface
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
	 * @var EventDispatcherInterface
	 */
	private $eventDispatcher;
	/**
	 * @var SettingsStorageInterface 
	 */
	private $settingsStorage;
	
	public function __construct(Connection $conn, Transaction $transaction, TimeFormatterInterface $timeFormatter, EventDispatcherInterface $eventDispatcher, SettingsStorageInterface $settingsStorage)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->timeFormatter = $timeFormatter;
		$this->eventDispatcher = $eventDispatcher;
		$this->settingsStorage = $settingsStorage;
	}
	
	/**
	 * @return DataTable
	 */
	public function createDataTable()
	{
		$dt = new DataTable();
		$dt->id('id', 'i.id')
			->searchableColumn('name', 'i.name')
			->column('createdAt', 'i.createdAt')
			->column('areasAllowed', 'i.areasAllowed')
			->column('areaRegistrationAllowed', 'i.areaRegistrationAllowed');
		return $dt;
	}
	
	public function listData(DataTable $dataTable)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.name', 'name')
			->field('i.createdAt', 'createdAt')
			->field('i.areasAllowed', 'areasAllowed')
			->field('i.areaRegistrationAllowed', 'areaRegistrationAllowed')
			->from(CoreTables::PROJECT_TBL, 'i')
			->where(QueryClause::clause('i.archived = 0'));	
		
		$recordsTotal = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(id)', 'cnt')
			->where($dataTable->buildCountingCondition($qb->getWhere()))
			->fetchCell($this->conn);
		$recordsFiltered = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(id)', 'cnt')
			->where($dataTable->buildFetchingCondition($qb->getWhere()))
			->fetchCell($this->conn);

		$qb->postprocess(function(array $row) { 
			$row['createdAtFormatted'] = $this->timeFormatter->ago($row['createdAt']);
			return $row;
		});

		$dataTable->processQuery($qb);
		return $dataTable->createAnswer(
			$recordsTotal,
			$recordsFiltered,
			$qb->where($dataTable->buildFetchingCondition($qb->getWhere()))->fetchAll($this->conn)
		);
	}
	
	/**
	 * @return Project
	 */
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		try {
			$item = Project::fetchActive($this->conn, $id);
			if (false === $item) {
				throw new ItemNotFoundException('The specified item has not been found.', $id);
			}
			return $item;
		} catch (\Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function insert(Project $project)
	{
		$this->transaction->requestTransaction();
		try {
			$id = $project->insert($this->conn);
			$settings = new ProjectSettings($this->settingsStorage);
			$settings->setProject($project);
			$this->eventDispatcher->dispatch(CantigaEvents::PROJECT_CREATED, new ProjectCreatedEvent($project, $settings));
			$settings->saveSettings();
			return $id;
		} catch(\Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function update(Project $project)
	{
		$this->transaction->requestTransaction();
		try {
			$project->update($this->conn);

			if ($project->isPendingArchivization()) {
				$this->eventDispatcher->dispatch(CantigaEvents::PROJECT_ARCHIVIZED, new ProjectArchivizedEvent($project));
			}
		} catch(\Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function getFormChoices()
	{
		$this->transaction->requestTransaction();
		$stmt = $this->conn->query('SELECT `id`, `name` FROM `'.CoreTables::PROJECT_TBL.'` WHERE `archived` = 0 ORDER BY `name`');
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