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

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\CourseBundle\CourseTables;
use Cantiga\CourseBundle\Entity\Course;
use Cantiga\CourseBundle\Entity\TestResult;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;

/**
 * Manages the area-related activities around courses, especially completing them.
 *
 * @author Tomasz Jędrzejewski
 */
class AreaCourseRepository
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
	 * @var Area
	 */
	private $area;
	
	public function __construct(Connection $conn, Transaction $transaction)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
	}
	
	public function setArea(Area $area)
	{
		$this->area = $area;
	}
	
	public function findAvailableCourses()
	{
		$items = $this->conn->fetchAll('SELECT c.`id`, c.`name`, c.`deadline`, r.`result`, r.`passedQuestions`, r.`totalQuestions`, r.`completedAt` '
			. 'FROM `'.CourseTables::COURSE_TBL.'` c '
			. 'LEFT JOIN `'.CourseTables::COURSE_RESULT_TBL.'` r ON (r.`courseId` = c.`id` AND r.`areaId` = :areaId) '
			. 'WHERE c.`isPublished` = 1 AND c.`projectId` = :projectId ORDER BY c.`displayOrder`', [':areaId' => $this->area->getId(), ':projectId' => $this->area->getProject()->getId()]);
		return $items;
	}
	
	public function getItem($id)
	{
		$item = Course::fetchPublished($this->conn, $id, $this->area->getProject());
		if (false === $item) {
			throw new ItemNotFoundException('The specified course has not been found.');
		}
		return $item;
	}

	public function getTestResult(Area $area, Course $course)
	{
		return TestResult::fetchResult($this->conn, $area, $course);
	}
}
