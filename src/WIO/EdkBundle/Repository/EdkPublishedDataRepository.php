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
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Form\EntityTransformerInterface;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use Exception;
use PDO;
use WIO\EdkBundle\EdkTables;
use WIO\EdkBundle\Entity\EdkRegistrationSettings;

class EdkPublishedDataRepository implements EntityTransformerInterface
{
	/**
	 * @var Connection 
	 */
	private $conn;
	/**
	 * @var Transaction
	 */
	private $transaction;
	private $project;
	private $publishedStatusId;
	
	public function __construct(Connection $conn, Transaction $transaction)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
	}
	
	public function setProject(Project $project)
	{
		$this->project = $project;
	}
	
	public function setPublishedStatusId($id)
	{
		$this->publishedStatusId = $id;
	}
	
	public function getArea($id): Area
	{
		$this->transaction->requestTransaction();
		try {
			$item = Area::fetchByProject($this->conn, $id, $this->project);
			if(false === $item || $item->getStatus()->getId() != $this->publishedStatusId || $item->getProject()->getArchived()) {
				$this->transaction->requestRollback();
				throw new ItemNotFoundException('The specified area has not been found.', $id);
			}
			return $item;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function getFormChoices()
	{
		$this->transaction->requestTransaction();
		$stmt = $this->conn->prepare('SELECT `id`, `name` FROM `'.CoreTables::AREA_TBL.'` WHERE `projectId` = :projectId AND `statusId` = :statusId ORDER BY `name`');
		$stmt->bindValue(':projectId', $this->project->getId());
		$stmt->bindValue(':statusId', $this->publishedStatusId);
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
		return $this->getArea($key);
	}

	public function transformToKey($entity)
	{
		return $entity->getId();
	}
	
	public function getOpenRegistrations(HierarchicalInterface $root, $acceptedStatus)
	{
		if ($root instanceof Project) {
			$rootPart = 'a.`projectId` = :rootId';
		} elseif ($root instanceof Group) {
			$rootPart = 'a.`groupId` = :rootId';
		} elseif ($root instanceof Area) {
			$rootPart = 'a.`id` = :rootId';
		}
		
		$stmt = $this->conn->prepare('SELECT r.`id` AS `routeId`, a.`id` AS `areaId`, t.`id` AS `territoryId`, r.`name` AS `routeName`, a.`name` AS `areaName`, t.`name` AS `territoryName`, s.`startTime`, s.`endTime`, s.`participantLimit`, s.`participantNum`, s.`allowLimitExceed`, s.`maxPeoplePerRecord`, s.`customQuestion`, r.`routeFrom`, r.`routeTo`, r.`routeLength`, r.`routeAscent`, r.`routeType` '
			. 'FROM `'.EdkTables::ROUTE_TBL.'` r '
			. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.`id` = r.`areaId` '
			. 'INNER JOIN `'.CoreTables::TERRITORY_TBL.'` t ON t.`id` = a.`territoryId` '
			. 'INNER JOIN `'.EdkTables::REGISTRATION_SETTINGS_TBL.'` s ON s.`routeId` = r.`id` '
			. 'WHERE a.`statusId` = :statusId AND '.$rootPart.' AND r.`approved` = 1 AND s.`registrationType` = '.EdkRegistrationSettings::TYPE_EDK_WEBSITE.' '
			. 'ORDER BY a.`name`, r.`name`');
		$stmt->bindValue(':statusId', $acceptedStatus);
		$stmt->bindValue(':rootId', $root->getId());
		$stmt->execute();
		
		$results = array();
		$now = time();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($now < $row['startTime'] || $row['endTime'] < $now) {
				continue;
			}
			if ($row['participantNum'] >= $row['participantLimit'] && !$row['allowLimitExceed']) {
				continue;
			}	
			if (!isset($results[$row['territoryId']])) {
				$results[$row['territoryId']] = ['id' => $row['territoryId'], 'name' => $row['territoryName'], 'areas' => []];
			}
			if (!isset($results[$row['territoryId']]['areas'][$row['areaId']])) {
				$results[$row['territoryId']]['areas'][$row['areaId']] = ['id' => $row['areaId'], 'name' => $row['areaName'], 'routes' => []];
			}
			if (!isset($results[$row['territoryId']]['areas'][$row['areaId']]['routes'][$row['routeId']])) {
				$results[$row['territoryId']]['areas'][$row['areaId']]['routes'][$row['routeId']] = [
					'id' => $row['routeId'],
					'name' => $row['routeName'],
					'from' => $row['routeFrom'],
					'to' => $row['routeTo'],
					'length' => $row['routeLength'],
					'ascent' => $row['routeAscent'],
					'q' => $row['customQuestion'],
					'pn' => $row['participantNum'],
					'pl' => $row['participantLimit'],
					'ppr' => $row['maxPeoplePerRecord'],
					't' => $row['routeType']];
			}
		}
		$stmt->closeCursor();
		usort($results, function($a, $b) {
			return strcmp($a['name'], $b['name']);
		});
		return $results;
	}
}
