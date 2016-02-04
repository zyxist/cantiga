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
}
