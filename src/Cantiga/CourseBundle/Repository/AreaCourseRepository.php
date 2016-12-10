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
namespace Cantiga\CourseBundle\Repository;

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\CourseBundle\CourseTables;
use Cantiga\CourseBundle\Entity\AreaCourseResult;
use Cantiga\CourseBundle\Entity\Course;
use Cantiga\CourseBundle\Entity\CourseProgress;
use Cantiga\CourseBundle\Entity\TestResult;
use Cantiga\CourseBundle\Entity\TestTrial;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Transaction;
use Cantiga\MilestoneBundle\Entity\NewMilestoneStatus;
use Cantiga\MilestoneBundle\Event\ActivationEvent;
use Cantiga\MilestoneBundle\MilestoneEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
	 * @var EventDispatcherInterface
	 */
	private $eventDispatcher;
	/**
	 * @var Area
	 */
	private $area;
	
	public function __construct(Connection $conn, Transaction $transaction, EventDispatcherInterface $eventDispatcher)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->eventDispatcher = $eventDispatcher;
	}
	
	public function setArea(Area $area)
	{
		$this->area = $area;
	}
	
	public function findAvailableCourses(User $user)
	{
		$items = $this->conn->fetchAll('SELECT c.`id`, c.`name`, c.`deadline`, r.`result` AS `user_result`, r.`passedQuestions` AS `user_passedQuestions`, '
			. 'r.`totalQuestions` AS `user_totalQuestions`, r.`completedAt` AS `user_completedAt`, arr.`result` AS `area_result`, arr.`passedQuestions` AS `area_passedQuestions`, '
			. 'arr.`totalQuestions` AS `area_totalQuestions`, arr.`completedAt` AS `area_completedAt` '
			. 'FROM `'.CourseTables::COURSE_TBL.'` c '
			. 'LEFT JOIN `'.CourseTables::COURSE_AREA_RESULT_TBL.'` ar ON (ar.`courseId` = c.`id` AND ar.`areaId` = :areaId) '
			. 'LEFT JOIN `'.CourseTables::COURSE_RESULT_TBL.'` arr ON (arr.`courseId` = ar.`courseId` AND arr.`userId` = ar.`userId`) '
			. 'LEFT JOIN `'.CourseTables::COURSE_RESULT_TBL.'` r ON (r.`courseId` = c.`id` AND r.`userId` = :userId) '
			. 'WHERE c.`isPublished` = 1 AND c.`projectId` = :projectId ORDER BY c.`displayOrder`', [':areaId' => $this->area->getId(), ':userId' => $user->getId(), ':projectId' => $this->area->getProject()->getId()]);
		foreach ($items as &$item) {
			TestResult::processResults($item, 'user_');
			TestResult::processResults($item, 'area_');
		}
		return $items;
	}
	
	public function getItem($id)
	{
		$item = Course::fetchPublished($this->conn, $id, $this->area->getProject());
		if (false === $item) {
			throw new ItemNotFoundException('CourseNotFoundMsg');
		}
		return $item;
	}

	public function getTestResult(User $user, Course $course)
	{
		return TestResult::fetchResult($this->conn, $user, $course);
	}
	
	public function getAreaResult(Area $area, Course $course)
	{
		return AreaCourseResult::fetchResult($this->conn, $area, $course);
	}
	
	public function startNewTrial(TestResult $result)
	{
		$this->transaction->requestTransaction();
		try {
			$result->startNewTrial($this->conn);
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function completeTrial(TestResult $result, Area $area, TestTrial $trial)
	{
		$this->transaction->requestTransaction();
		try {
			$output = $result->completeTrial($this->conn, $area, $trial);
			$this->spawnActivationEvent($area, $output);
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function confirmGoodFaithCompletion(Area $area, User $user, Course $course)
	{
		$this->transaction->requestTransaction();
		try {
			$output = $course->confirmGoodFaithCompletion($this->conn, $area, $user);
			$this->spawnActivationEvent($area, $output);
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function findProgress()
	{
		return CourseProgress::fetchByArea($this->conn, $this->area);
	}
	
	private function spawnActivationEvent(Area $area, $output)
	{
		if ($output instanceof CourseProgress) {
			$this->eventDispatcher->dispatch(MilestoneEvents::ACTIVATION_EVENT, new ActivationEvent($area->getProject(), $area->getPlace(), 'course.completed', function() use($output) {
				if ($output->getMandatoryCourseNum() == 0) {
					return NewMilestoneStatus::create(100);
				} else {
					return NewMilestoneStatus::create((int)($output->getPassedCourseNum() / $output->getMandatoryCourseNum() * 100));
				}
			}));
		}
	}
}
