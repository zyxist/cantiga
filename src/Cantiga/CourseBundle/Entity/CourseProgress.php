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
namespace Cantiga\CourseBundle\Entity;

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\CourseBundle\CourseTables;
use Doctrine\DBAL\Connection;
use Exception;

/**
 * Information about course result summary for the given area.
 *
 * @author Tomasz Jędrzejewski
 */
class CourseProgress implements InsertableEntityInterface
{
	private $area;
	private $mandatoryCourseNum = 0;
	private $passedCourseNum = 0;
	private $failedCourseNum = 0;
	
	public static function fetchByArea(Connection $conn, Area $area)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.CourseTables::COURSE_PROGRESS_TBL.'` WHERE `areaId` = :areaId', [':areaId' => $area->getId()]);
		if (false === $data) {
			return false;
		}
		$item = new CourseProgress($area);
		$item->mandatoryCourseNum = $data['mandatoryCourseNum'];
		$item->passedCourseNum = $data['passedCourseNum'];
		$item->failedCourseNum = $data['failedCourseNum'];
		return $item;
	}

	public function __construct(Area $area)
	{
		$this->area = $area;
	}
	
	public function getMandatoryCourseNum()
	{
		return $this->mandatoryCourseNum;
	}

	public function getPassedCourseNum()
	{
		return $this->passedCourseNum;
	}

	public function getFailedCourseNum()
	{
		return $this->failedCourseNum;
	}

	public function insert(Connection $conn)
	{
		$this->mandatoryCourseNum = $conn->fetchColumn('SELECT COUNT(`id`) FROM `'.CourseTables::COURSE_TBL.'` WHERE `isPublished` = 1');
			
		$conn->insert(CourseTables::COURSE_PROGRESS_TBL, array(
			'areaId' => $this->area->getId(),
			'mandatoryCourseNum' => $this->mandatoryCourseNum,
			'passedCourseNum' => 0,
			'failedCourseNum' => 0
		));
	}
	
	/**
	 * Updates the progress summary for the given area.
	 * 
	 * @param Connection $conn
	 * @param AbstractTestResult $result
	 * @param TestTrial $trial
	 */
	public function updateResults(Connection $conn, AbstractTestResult $result, TestTrial $trial)
	{
		$this->refresh($conn);
		if($trial->getResult() == Question::RESULT_CORRECT) {
			$this->passedCourseNum++;
		} elseif($trial->getResult() == Question::RESULT_INVALID) {
			$this->failedCourseNum++;
		}
			
		if($result->getResult() == Question::RESULT_INVALID) {
			$this->failedCourseNum--;
		} elseif($result->getResult() == Question::RESULT_CORRECT) {
			$this->passedCourseNum--;
		}

		$conn->update(CourseTables::COURSE_PROGRESS_TBL, [
			'passedCourseNum' => $this->passedCourseNum,
			'failedCourseNum' => $this->failedCourseNum
		], ['areaId' => $this->area->getId()]);
	}
	
	/**
	 * For confirming and counting trainngs without a test.
	 * 
	 * @throws Exception
	 */
	public function updateGoodFaithCompletion(Connection $conn)
	{
		$this->passedCourseNum++;
		$conn->update(CourseTables::COURSE_PROGRESS_TBL, [
			'passedCourseNum' => $this->passedCourseNum,
			'failedCourseNum' => $this->failedCourseNum
		], ['areaId' => $this->area->getId()]);
		$this->refresh($conn);
	}
	
	public function refresh(Connection $conn)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.CourseTables::COURSE_PROGRESS_TBL.'` WHERE `areaId` = :id', array(':id' => $this->area->getId()));
		$this->mandatoryCourseNum = $data['mandatoryCourseNum'];
		$this->passedCourseNum = $data['passedCourseNum'];
		$this->failedCourseNum = $data['failedCourseNum'];
	}
}
