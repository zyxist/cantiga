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
use Cantiga\ExportBundle\Entity\ExportBlock;
use Cantiga\ExportBundle\Event\ExportEvent;
use Cantiga\ExportBundle\ExportEvents;
use Cantiga\ExportBundle\ExportTables;
use Doctrine\DBAL\Connection;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Service for exporting the data to the external systems.
 *
 * @author Tomasz Jędrzejewski
 */
class ExportEngine
{
	/**
	 * @var Connection
	 */
	private $conn;
	/**
	 * @var EventDispatcherInterface
	 */
	private $eventDispatcher;
	
	public function __construct(Connection $conn, EventDispatcherInterface $eventDispatcher)
	{
		$this->conn = $conn;
		$this->eventDispatcher = $eventDispatcher;
	}
	
	public function findActiveExports()
	{
		return $this->conn->fetchAll('SELECT * FROM `'.ExportTables::DATA_EXPORT_TBL.'` WHERE `active` = 1');
	}
	
	public function exportData($export, $reporter)
	{
		$this->conn->beginTransaction();
		try {
			$lastExportedAt = (int) $export['lastExportedAt'];
			
			$areas = $this->conn->fetchAll('SELECT a.`id`, a.`name`, t.`id` AS `territoryId`, t.`name` AS `territoryName`, a.`customData`, a.`lastUpdatedAt` '
				. 'FROM `'.CoreTables::AREA_TBL.'` a '
				. 'INNER JOIN `'.CoreTables::TERRITORY_TBL.'` t ON t.`id` = a.`territoryId` '
				. 'WHERE a.`projectId` = :projectId AND a.`statusId` = :statusId', [':projectId' => $export['projectId'], ':statusId' => $export['areaStatusId']]);
			
			
			$block = new ExportBlock();
			
			foreach ($areas as $area) {
				$block->addId($area['id']);
				if ($area['lastUpdatedAt'] > $lastExportedAt) {
					$area['customData'] = json_decode($area['customData']);
					$block->addUpdatedId($area['id']);
					$block->addUpdate($area);
				}
			}
			
			$event = new ExportEvent($export['projectId'], $export['lastExportedAt'], $reporter);
			$event->addBlock('area', $block);
			
			$event = $this->eventDispatcher->dispatch(ExportEvents::EXPORT_ONGOING, $event);
			
			$this->conn->executeQuery('UPDATE `'.ExportTables::DATA_EXPORT_TBL.'` SET `lastExportedAt` = :time WHERE `id` = :id', [':time' => time(), ':id' => $export['id']]);
			$this->conn->commit();
			
			return $event->output();
		} catch (Exception $ex) {
			$this->conn->rollBack();
			throw $ex;
		}
	}
}
