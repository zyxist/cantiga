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
namespace Cantiga\CoreBundle\Command;

use Cantiga\CoreBundle\CoreTables;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for archivizing the projects. Because this is a potentially destructive operation for data,
 * we do not allow to perform it directly from the panel.
 *
 * @author Tomasz Jędrzejewski
 */
class SyncCountersCommand extends ContainerAwareCommand
{

	protected function configure()
	{
		$this
			->setName('cantiga:db:sync-counters')
			->setDescription('Synchronizes the counters with the actual database state.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$conn = $this->getContainer()->get('database_connection');
			
		$this->fixGroupAreaCounter($conn);
		$this->fixGroupStatusCounter($conn);
		$this->fixGroupTerritoryCounter($conn);
	}
	
	private function fixGroupAreaCounter(Connection $conn)
	{
		$items = $conn->fetchAll('SELECT `groupId`, COUNT(`id`) AS `count` FROM  `'.CoreTables::AREA_TBL.'` GROUP BY  `groupId` ');
			
		$stmt = $conn->prepare('UPDATE `'.CoreTables::GROUP_TBL.'` SET `areaNum` = :areaNum WHERE `id` = :id');
		foreach ($items as $item) {
			if (!empty($item['groupId'])) {
				$stmt->bindValue(':areaNum', $item['count']);
				$stmt->bindValue(':id', $item['groupId']);
				$stmt->execute();
			}
		}
	}
	
	private function fixGroupStatusCounter(Connection $conn)
	{
		$items = $conn->fetchAll('SELECT `statusId`, COUNT(`id`) AS `count` FROM  `'.CoreTables::AREA_TBL.'` GROUP BY  `statusId` ');
			
		$stmt = $conn->prepare('UPDATE `'.CoreTables::AREA_STATUS_TBL.'` SET `areaNum` = :areaNum WHERE `id` = :id');
		foreach ($items as $item) {
			if (!empty($item['statusId'])) {
				$stmt->bindValue(':areaNum', $item['count']);
				$stmt->bindValue(':id', $item['statusId']);
				$stmt->execute();
			}
		}
	}
	
	private function fixGroupTerritoryCounter(Connection $conn)
	{
		$items = $conn->fetchAll('SELECT `territoryId`, COUNT(`id`) AS `count` FROM  `'.CoreTables::AREA_TBL.'` GROUP BY  `territoryId` ');
			
		$stmt = $conn->prepare('UPDATE `'.CoreTables::TERRITORY_TBL.'` SET `areaNum` = :areaNum WHERE `id` = :id');
		foreach ($items as $item) {
			if (!empty($item['statusId'])) {
				$stmt->bindValue(':areaNum', $item['count']);
				$stmt->bindValue(':id', $item['statusId']);
				$stmt->execute();
			}
		}
	}
}
