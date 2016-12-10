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
namespace Cantiga\MilestoneBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\Metamodel\Transaction;
use Cantiga\MilestoneBundle\MilestoneTables;
use Doctrine\DBAL\Connection;

/**
 * Performs the bulk update of area status according to the specified rules.
 *
 * @author Tomasz Jędrzejewski
 */
class UpdateRepository
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
	
	public function findUpdatableProjects()
	{
		return $this->conn->fetchAll('SELECT `id`, `name` FROM `'.CoreTables::PROJECT_TBL.'` WHERE `archived` = 0');
	}
	
	public function runUpdateForProject($projectId)
	{
		$rules = $this->conn->fetchAll('SELECT `id`, `newStatusId`, `prevStatusId`, `milestoneMap` '
			. 'FROM `'.MilestoneTables::MILESTONE_STATUS_RULE_TBL.'` '
			. 'WHERE `projectId` = :projectId ORDER BY `activationOrder`', [':projectId' => $projectId]);
		foreach ($rules as &$rule) {
			$rule['milestoneMap'] = explode(',', $rule['milestoneMap']);
		}
		
		$reverseRules = array_reverse($rules);
		
		$rawAreas = $this->conn->fetchAll('SELECT a.`id` AS `areaId`, a.`statusId`, m.`milestoneId` '
			. 'FROM `'.CoreTables::AREA_TBL.'` a '
			. 'INNER JOIN `'.MilestoneTables::MILESTONE_STATUS_TBL.'` m ON m.`entityId` = a.`placeId` '
			. 'WHERE a.`projectId` = :projectId AND m.`progress` = 100', [':projectId' => $projectId]);
		
		$areas = [];
		foreach ($rawAreas as $rawArea) {
			if (!isset($areas[$rawArea['areaId']])) {
				$areas[$rawArea['areaId']] = [
					'id' => $rawArea['areaId'],
					'statusId' => $rawArea['statusId'],
					'milestones' => []
				];
			}
			$areas[$rawArea['areaId']]['milestones'][] = $rawArea['milestoneId'];
		}
		return $this->applyChanges($areas, $rules, $reverseRules);
	}
	
	private function applyChanges($areas, $rules, $reverseRules)
	{
		$stmt = $this->conn->prepare('UPDATE `'.CoreTables::AREA_TBL.'` SET `statusId` = :statusId, `lastUpdatedAt` = :lastUpdated WHERE `id` = :id');
		$count = 0;
		foreach ($areas as $area) {
			foreach ($rules as $rule) {
				if ($this->isMatching($area, $rule)) {
					$stmt->bindValue(':id', $area['id']);
					$stmt->bindValue(':statusId', $rule['newStatusId']);
					$stmt->bindValue(':lastUpdated', time());
					$stmt->execute();
					$count++;
					continue 2;
				}
			}
			foreach ($reverseRules as $rule) {
				if ($this->isNotMatching($area, $rule)) {
					$stmt->bindValue(':id', $area['id']);
					$stmt->bindValue(':statusId', $rule['prevStatusId']);
					$stmt->bindValue(':lastUpdated', time());
					$stmt->execute();
					$count++;
					continue 2;
				}
			}
		}
		return $count;
	}
	
	private function isMatching(array $area, array $rule)
	{
		if ($area['statusId'] == $rule['prevStatusId']) {
			foreach ($rule['milestoneMap'] as $milestoneId) {
				if (!in_array($milestoneId, $area['milestones'])) {
					return false;
				}
			}
			return true;
		}
		return false;
	}
	
	private function isNotMatching(array $area, array $rule)
	{
		if ($area['statusId'] == $rule['newStatusId']) {
			foreach ($rule['milestoneMap'] as $milestoneId) {
				if (!in_array($milestoneId, $area['milestones'])) {
					return true;
				}
			}
		}
		return false;
	}
}
