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
namespace Cantiga\MilestoneBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\Transaction;
use Cantiga\MilestoneBundle\MilestoneTables;
use Doctrine\DBAL\Connection;

/**
 * @author Tomasz Jędrzejewski
 */
class MilestoneSummaryRepository
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
	
	public function findMilestoneProgressForAreasInProject(Project $project)
	{
		$totalMilestones = $this->conn->fetchColumn('SELECT COUNT(`id`) FROM `'.MilestoneTables::MILESTONE_TBL.'` WHERE `projectId` = :projectId AND `entityType` = \'Area\'', [':projectId' => $project->getId()]);	
		$results = $this->conn->fetchAll('SELECT a.`id`, a.`name`, a.`entityId`, p.`completedNum` '
			. 'FROM `'.CoreTables::AREA_TBL.'` a '
			. 'INNER JOIN `'.MilestoneTables::MILESTONE_PROGRESS_TBL.'` p ON p.`entityId` = a.`entityId` '
			. 'WHERE a.`projectId` = :projectId '
			. 'ORDER BY p.`completedNum` DESC, a.`name`', [':projectId' => $project->getId()]);
		foreach ($results as &$result) {
			$this->processResult($totalMilestones, $result);
		}
		return $results;
	}
	
	public function findMilestoneProgressForGroupsInProject(Project $project)
	{
		$totalMilestones = $this->conn->fetchColumn('SELECT COUNT(`id`) FROM `'.MilestoneTables::MILESTONE_TBL.'` WHERE `projectId` = :projectId AND `entityType` = \'Group\'', [':projectId' => $project->getId()]);	
		$results = $this->conn->fetchAll('SELECT g.`id`, g.`name`, g.`entityId`, p.`completedNum` '
			. 'FROM `'.CoreTables::GROUP_TBL.'` g '
			. 'INNER JOIN `'.MilestoneTables::MILESTONE_PROGRESS_TBL.'` p ON p.`entityId` = g.`entityId` '
			. 'WHERE g.`projectId` = :projectId '
			. 'ORDER BY p.`completedNum` DESC, g.`name`', [':projectId' => $project->getId()]);
		foreach ($results as &$result) {
			$this->processResult($totalMilestones, $result);
		}
		return $results;
	}
	
	public function findMilestoneProgressForAreasInGroup(Group $group)
	{
		$totalMilestones = $this->conn->fetchColumn('SELECT COUNT(`id`) FROM `'.MilestoneTables::MILESTONE_TBL.'` WHERE `projectId` = :projectId AND `entityType` = \'Area\'', [':projectId' => $group->getProject()->getId()]);	
		$results = $this->conn->fetchAll('SELECT a.`id`, a.`name`, a.`entityId`, p.`completedNum` '
			. 'FROM `'.CoreTables::AREA_TBL.'` a '
			. 'INNER JOIN `'.MilestoneTables::MILESTONE_PROGRESS_TBL.'` p ON p.`entityId` = a.`entityId` '
			. 'WHERE a.`groupId` = :groupId '
			. 'ORDER BY p.`completedNum` DESC, a.`name`', [':groupId' => $group->getId()]);
		foreach ($results as &$result) {
			$this->processResult($totalMilestones, $result);
		}
		return $results;
	}
	
	private function processResult($totalMilestones, array &$result)
	{
		if ($totalMilestones == 0) {
			$result['progress'] = 0;
		} else {
			$result['progress'] = round($result['completedNum'] / $totalMilestones * 100);
		}
		if ($result['progress'] < 50) {
			$result['progressColor'] = 'danger';
			$result['badgeColor'] = 'red';
		} elseif ($result['progress'] < 80) {
			$result['progressColor'] = 'warning';
			$result['badgeColor'] = 'orange';
		} else {
			$result['progressColor'] = 'success';
			$result['badgeColor'] = 'green';
		}
	}
}
