<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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

use Cantiga\CoreBundle\CoreTables;
use Cantiga\ExportBundle\Entity\ExportBlock;
use Doctrine\DBAL\Connection;
use PDO;
use WIO\EdkBundle\EdkTables;

/**
 * @author Tomasz Jędrzejewski
 */
class EdkExportRepository
{
	/**
	 * @var Connection
	 */
	private $conn;
	
	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}
	
	public function exportTerritories($projectId)
	{
		$stmt = $this->conn->prepare('SELECT `id`, `name` FROM `'.CoreTables::TERRITORY_TBL.'` WHERE `projectId` = :id');
		$stmt->bindValue(':id', $projectId);
		$stmt->execute();
		
		$block = new ExportBlock();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$block->addId($row['id']);
			$block->addUpdate($row);
		}
		$stmt->closeCursor();
		return $block;
	}
	
	public function exportAreaDescriptions($lastExport, $areaIds)
	{
		$block = new ExportBlock();
		if (sizeof($areaIds) > 0) {
			$stmt = $this->conn->query('SELECT `areaId`, `noteType`, `content`, `lastUpdatedAt`  FROM `'.EdkTables::AREA_NOTE_TBL.'` WHERE `areaId` IN ('.implode(',', $areaIds).') ');
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$block->addId(['areaId' => $row['areaId'], 'noteType' => $row['noteType']]);
				if ($row['lastUpdatedAt'] > $lastExport) {
					$block->addUpdate($row);
				}
			}
			$stmt->closeCursor();
		}
		return $block;
	}
	
	public function exportRoutes($lastExport, $areaIds)
	{
		$block = new ExportBlock();
		if (sizeof ($areaIds) > 0) {
			$stmt = $this->conn->query('SELECT r.*, a.territoryId, x.registrationType, x.startTime, x.endTime, x.externalRegistrationUrl FROM `'.EdkTables::ROUTE_TBL.'` r '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.`id` = r.`areaId` '
				. 'LEFT JOIN `'.EdkTables::REGISTRATION_SETTINGS_TBL.'` x ON x.`routeId` = r.`id` '
				. 'WHERE r.`areaId` IN ('.implode(',', $areaIds).') AND r.`approved` = 1');
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$block->addId($row['id']);
				if ($row['updatedAt'] > $lastExport) {
					$block->addUpdate($row);
				}
			}
			$stmt->closeCursor();
		}
		return $block;
	}
	
	public function exportRouteDescriptions($lastExport, $routeIds)
	{
		$block = new ExportBlock();
		if (sizeof ($routeIds) > 0) {
			$stmt = $this->conn->query('SELECT `routeId`, `noteType`, `content`, `lastUpdatedAt`  FROM `'.EdkTables::ROUTE_NOTE_TBL.'` WHERE `routeId` IN ('.implode(',', $routeIds).') ');
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$block->addId(['routeId' => $row['routeId'], 'noteType' => $row['noteType']]);
				if ($row['lastUpdatedAt'] > $lastExport) {
					$block->addUpdate($row);
				}
			}
			$stmt->closeCursor();
		}
		return $block;
	}
	
	public function prepareMailSummary()
	{
		$stmt = $this->conn->query('SELECT a.`id`, a.`name`, t.`locale`, p.`modules`, s.`name` AS `statusName` FROM `'.CoreTables::AREA_TBL.'` a '
			. 'INNER JOIN `'.CoreTables::AREA_STATUS_TBL.'` s ON s.`id` = a.`statusId` '
			. 'INNER JOIN `'.CoreTables::TERRITORY_TBL.'` t ON t.`id` = a.`territoryId` '
			. 'INNER JOIN `'.CoreTables::PROJECT_TBL.'` p ON p.`id` = a.`projectId` '
			. 'WHERE p.`archived` = 0');
		$areaIds = [];
		$areas = [];
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if (strpos($row['modules'], 'edk') !== false) {
				$areaIds[] = $row['id'];
				$areas[$row['id']] = [
					'id' => $row['id'],
					'locale' => $row['locale'],
					'name' => $row['name'],
					'statusName' => $row['statusName'],
					'messages' => [0 => 0, 1 => 0, 2 => 0, 3 => 0],
					'emails' => [],
					'newMessages' => 0,
					'participants' => 0,
				];
			}
		}
		$stmt->closeCursor();
		if (sizeof($areaIds) > 0) {
			$stmt = $this->conn->query('SELECT `areaId`, `status`, COUNT(`id`) AS `total` FROM `'.EdkTables::MESSAGE_TBL.'` WHERE `areaId` IN ('.implode(',', $areaIds).') GROUP BY `areaId`, `status`');
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$areas[$row['areaId']]['messages'][$row['status']] = $row['total'];
			}
			$stmt->closeCursor();
			
			$stmt = $this->conn->prepare('SELECT `areaId`, COUNT(`id`) AS `total` FROM `'.EdkTables::MESSAGE_TBL.'` WHERE `areaId` IN ('.implode(',', $areaIds).') AND `createdAt` > :time GROUP BY `areaId`');
			$stmt->bindValue(':time', time() - 86400);
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$areas[$row['areaId']]['newMessages'] = $row['total'];
			}
			$stmt->closeCursor();
			
			$stmt = $this->conn->prepare('SELECT `areaId`, SUM(`participantNum`) AS `total` FROM `'.EdkTables::REGISTRATION_SETTINGS_TBL.'` WHERE `areaId` IN ('.implode(',', $areaIds).') GROUP BY `areaId`');
			$stmt->bindValue(':time', time() - 86400);
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$areas[$row['areaId']]['participants'] = $row['total'];
			}
			$stmt->closeCursor();
			
			$stmt = $this->conn->prepare('SELECT u.`email`, m.`areaId` FROM `'.CoreTables::USER_TBL.'` u INNER JOIN `'.CoreTables::AREA_MEMBER_TBL.'` m ON m.`userId` = u.`id` WHERE m.`areaId` IN ('.implode(',', $areaIds).') ');
			$stmt->bindValue(':time', time() - 86400);
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$areas[$row['areaId']]['emails'][] = $row['email'];
			}
			$stmt->closeCursor();
		}
		return $areas;
	}
}
