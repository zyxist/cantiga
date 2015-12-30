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
namespace Cantiga\CourseBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CourseBundle\CourseTables;
use Cantiga\CourseBundle\Entity\TestResult;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;

/**
 * Manages the area-related activities around courses, especially completing them.
 *
 * @author Tomasz Jędrzejewski
 */
class SummaryRepository
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
	
	public function findTotalIndividualResultsForArea(Area $area)
	{
		$items = $this->conn->fetchAll('SELECT c.`id` AS `courseId`, c.`name` AS `courseName`, u.`id` AS `userId`, u.`name` AS `userName`, u.`avatar`, '
			. 'ur.`result`, ur.`totalQuestions`, ur.`passedQuestions`, ur.`completedAt`, ur.`trialNumber` '
			. 'FROM `'.CourseTables::COURSE_TBL.'` c '
			. 'INNER JOIN `'.CoreTables::AREA_MEMBER_TBL.'` m ON m.`areaId` = :areaId '
			. 'INNER JOIN `'.CoreTables::USER_TBL.'` u ON u.`id` = m.`userId` '
			. 'LEFT JOIN `'.CourseTables::COURSE_RESULT_TBL.'` ur ON ur.`userId` = u.`id` AND c.`id` = ur.`courseId` '
			. 'WHERE c.`isPublished` = 1 AND u.`active` = 1 AND c.`projectId` = :projectId '
			. 'ORDER BY c.`displayOrder`, u.`name`', [':areaId' => $area->getId(), ':projectId' => $area->getProject()->getId()]);
		foreach ($items as &$item) {
			TestResult::processResults($item);
		}
		return $items;
	}
}
