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

use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\Components\Hierarchy\MembershipEntityInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\Transaction;
use Cantiga\MilestoneBundle\Entity\Milestone;
use Cantiga\MilestoneBundle\MilestoneTables;
use Doctrine\DBAL\Connection;
use PDO;

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
		$results = $this->conn->fetchAll('SELECT a.`id`, a.`name`, a.`placeId`, p.`completedNum` '
			. 'FROM `'.CoreTables::AREA_TBL.'` a '
			. 'INNER JOIN `'.MilestoneTables::MILESTONE_PROGRESS_TBL.'` p ON p.`entityId` = a.`placeId` '
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
		$results = $this->conn->fetchAll('SELECT g.`id`, g.`name`, g.`placeId`, p.`completedNum` '
			. 'FROM `'.CoreTables::GROUP_TBL.'` g '
			. 'INNER JOIN `'.MilestoneTables::MILESTONE_PROGRESS_TBL.'` p ON p.`entityId` = g.`placeId` '
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
		$results = $this->conn->fetchAll('SELECT a.`id`, a.`name`, a.`placeId`, p.`completedNum` '
			. 'FROM `'.CoreTables::AREA_TBL.'` a '
			. 'INNER JOIN `'.MilestoneTables::MILESTONE_PROGRESS_TBL.'` p ON p.`entityId` = a.`placeId` '
			. 'WHERE a.`groupId` = :groupId '
			. 'ORDER BY p.`completedNum` DESC, a.`name`', [':groupId' => $group->getId()]);
		foreach ($results as &$result) {
			$this->processResult($totalMilestones, $result);
		}
		return $results;
	}
	
	/**
	 * Shows all the areas and the completeness of all the milestones in a simple grid.
	 * 
	 * @param MembershipEntityInterface $parent
	 */
	public function findTotalAreaCompleteness(HierarchicalInterface $parent)
	{
		$whereExtras = '';
		if ($parent instanceof Project) {
			$whereExtras = ' AND a.`projectId` = :itemId ';
		} else {
			$whereExtras = ' AND a.`groupId` = :itemId ';
		}
		
		$stmt = $this->conn->prepare('SELECT a.`id`, a.`placeId`, a.`name`, t.`name` AS `statusName`, t.`label` AS `statusLabel`, m.`id` AS `milestoneId`, m.`name` AS `milestoneName`, s.`progress` '
			. 'FROM `'.CoreTables::AREA_TBL.'` a '
			. 'INNER JOIN `'.CoreTables::AREA_STATUS_TBL.'` t ON t.`id` = a.`statusId` '
			. 'INNER JOIN `'.MilestoneTables::MILESTONE_STATUS_TBL.'` s ON s.`entityId` = a.`placeId` '
			. 'INNER JOIN `'.MilestoneTables::MILESTONE_TBL.'` m ON m.`id` = s.`milestoneId` '
			. 'WHERE m.`entityType` = \'Area\' '.$whereExtras
			. 'ORDER BY a.`name`, m.`id` ');
		$stmt->bindValue(':itemId', $parent->getId());
		$stmt->execute();
		
		$milestones = array();
		$results = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if (!isset($results[$row['id']])) {
				$results[$row['id']] = [
					'id' => $row['id'],
					'entityId' => $row['placeId'],
					'name' => $row['name'],
					'status' => $row['statusName'],
					'label' => $row['statusLabel'],
					'milestones' => [0 => $row['progress']],
				];
			} else {
				$results[$row['id']]['milestones'][] = $row['progress'];
			}
			$milestones[$row['milestoneId']] = $row['milestoneName'];
		}
		$stmt->closeCursor();
		
		return [$milestones, $results];
	}
	
	private function processResult($totalMilestones, array &$result)
	{
		if ($totalMilestones == 0) {
			$result['progress'] = 0;
		} else {
			$result['progress'] = round($result['completedNum'] / $totalMilestones * 100);
		}
		$result['progressColor'] = Milestone::getProgressColor($result['progress']);
		$result['badgeColor'] = Milestone::getBadgeColor($result['progress']);
	}
}
