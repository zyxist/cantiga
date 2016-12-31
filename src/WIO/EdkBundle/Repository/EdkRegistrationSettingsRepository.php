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
namespace WIO\EdkBundle\Repository;

use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\Capabilities\EditableRepositoryInterface;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Form\EntityTransformerInterface;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use Exception;
use Symfony\Component\Translation\TranslatorInterface;
use WIO\EdkBundle\EdkTables;
use WIO\EdkBundle\Entity\EdkRegistrationSettings;
use WIO\EdkBundle\Entity\EdkRoute;

/**
 * Manages the list of active registration settings for the given area/group/project.
 */
class EdkRegistrationSettingsRepository implements EditableRepositoryInterface, EntityTransformerInterface
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
	 * @var Project|Group|Area
	 */
	private $root;
	
	public function __construct(Connection $conn, Transaction $transaction, TimeFormatterInterface $timeFormatter)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->timeFormatter = $timeFormatter;
	}
	
	public function setRootEntity(HierarchicalInterface $root)
	{
		$this->root = $root;
	}
	
	/**
	 * @return DataTable
	 */
	public function createDataTable()
	{
		$dt = new DataTable();
		$dt->id('id', 'i.id');
		
		if (!($this->root instanceof Area)) {
			$dt->searchableColumn('areaName', 'a.name');
		}
		$dt
			->searchableColumn('name', 'i.name')
			->searchableColumn('registrationType', 'r.registrationType')
			->searchableColumn('startTime', 'r.startTime')
			->column('endTime', 'r.endTime')
			->column('participantLimit', 'r.participantLimit')
			->column('participantNum', 'r.participantNum');
		return $dt;
	}
	
	public function listData(DataTable $dataTable, TranslatorInterface $translator)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id');
		
		if (!($this->root instanceof Area)) {
			$qb->field('a.id', 'areaId');
			$qb->field('a.name', 'areaName');
			$qb->join(CoreTables::AREA_TBL, 'a', QueryClause::clause('a.id = i.areaId'));
		}
		$qb
			->field('i.name', 'name')
			->field('r.registrationType', 'registrationType')
			->field('r.startTime', 'startTime')
			->field('r.endTime', 'endTime')
			->field('r.participantLimit', 'participantLimit')
			->field('r.participantNum', 'participantNum')
			->from(EdkTables::ROUTE_TBL, 'i')
			->leftJoin(EdkTables::REGISTRATION_SETTINGS_TBL, 'r', QueryClause::clause('r.routeId = i.id'))
			->where(QueryClause::clause('i.approved = 1'));
		
		if ($this->root instanceof Area) {
			$qb->where(QueryClause::clause('i.`areaId` = :areaId', ':areaId', $this->root->getId()));
		} elseif ($this->root instanceof Group) {
			$qb->where(QueryClause::clause('a.`groupId` = :groupId', ':groupId', $this->root->getId()));
		} elseif ($this->root instanceof Project) {
			$qb->where(QueryClause::clause('a.`projectId` = :projectId', ':projectId', $this->root->getId()));
		}

		$qb->postprocess(function($row) use ($translator) {
			if (empty($row['startTime'])) {
				$row['startTime'] = '--';
			} else {
				$row['startTime'] = $this->timeFormatter->format(TimeFormatterInterface::FORMAT_DATE_SHORT, $row['startTime']);
			}
			if (empty($row['endTime'])) {
				$row['endTime'] = '--';
			} else {
				$row['endTime'] = $this->timeFormatter->format(TimeFormatterInterface::FORMAT_DATE_SHORT, $row['endTime']);
			}
			$row['registrationTypeText'] = $translator->trans(EdkRegistrationSettings::registrationTypeText($row['registrationType']), [], 'edk');
			return $row;
		});
		
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
	 * @return EdkRegistrationSettings
	 */
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		try {
			$route = EdkRoute::fetchByRoot($this->conn, $id, $this->root);
			
			if (empty($route)) {
				throw new ItemNotFoundException('The specified route has not been found.');
			}
			
			return EdkRegistrationSettings::fetchByRoute($this->conn, $route);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function update($item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->update($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function countParticipants()
	{
		$this->transaction->requestTransaction();
		try {
			return $this->conn->fetchColumn('SELECT SUM(s.`participantNum`) + SUM(s.`externalParticipantNum`) '
				. 'FROM `'.EdkTables::REGISTRATION_SETTINGS_TBL.'` s '
				. 'INNER JOIN `'.EdkTables::ROUTE_TBL.'` r ON r.`id` = s.`routeId` '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON r.`areaId` = a.`id` '
				. $this->createWhereClause(), [':itemId' => $this->root->getId()]);
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	private function createWhereClause()
	{
		if ($this->root instanceof Area) {
			return 'WHERE r.`areaId` = :itemId ';				
		} elseif ($this->root instanceof Group) {
			return 'WHERE a.`groupId` = :itemId ';
		} elseif ($this->root instanceof Project) {
			return 'WHERE a.`projectId` = :itemId ';
		}
	}

	public function transformToEntity($key)
	{
		if (null !== $key) {
			return $this->getItem($key);
		}
		return null;
	}

	public function transformToKey($entity)
	{
		if (!empty($entity)) {
			return $entity->getId();
		}
		return null;
	}
}
