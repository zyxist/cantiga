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
namespace Cantiga\CourseBundle\Tests\Entity;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\AreaStatus;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\Territory;
use Cantiga\CoreBundle\Tests\Utils\DatabaseTestCase;
use Cantiga\CourseBundle\Entity\Question;
use Cantiga\CourseBundle\Entity\TestResult;
use Cantiga\CourseBundle\Entity\TestTrial;
use Cantiga\CourseBundle\Entity\CourseProgress;
use Cantiga\CourseBundle\CourseTables;

class TestProgressTest extends DatabaseTestCase
{
	private $area;
	
	protected static function customSetup()
	{
		self::importFixture('course-test.sql');
	}
	
	public function setUp()
	{
		$this->project = Project::fetch(self::$conn, 1);
		$this->status = AreaStatus::fetchByProject(self::$conn, 1, $this->project);
		$this->territory = Territory::fetchByProject(self::$conn, 1, $this->project);
		
		$this->area = Area::newArea($this->project, $this->territory, $this->status, 'Area1');
		$this->area->insert(self::$conn);
		
		$pp = new CourseProgress($this->area);
		$pp->insert(self::$conn);
	}
	
	public function tearDown()
	{
		self::$conn->executeUpdate('DELETE FROM `'.CourseTables::COURSE_RESULT_TBL.'`');
		self::$conn->executeUpdate('DELETE FROM `'.CoreTables::USER_TBL.'`');
		self::$conn->executeUpdate('DELETE FROM `'.CourseTables::COURSE_TBL.'`');
	}
	
	public function testUpdatingResultsFromIncompleteToPassed()
	{
		// Given
		$record = CourseProgress::fetchByArea(self::$conn, $this->area);
		$result = $this->getTestResult(Question::RESULT_UNKNOWN);
		$trial = $this->getTestTrial(Question::RESULT_CORRECT);
		$this->fillRecord($this->area, 7, 3, 3);
		
		// When
		$record->updateResults(self::$conn, $result, $trial);
		
		// Then
		$this->expectCourseProgress($this->area, 7, 4, 3);
		
	}
	
	public function testUpdatingResultsFromIncompleteToFailed()
	{
		// Given
		$record = CourseProgress::fetchByArea(self::$conn, $this->area);
		$result = $this->getTestResult(Question::RESULT_UNKNOWN);
		$trial = $this->getTestTrial(Question::RESULT_INVALID);
		$this->fillRecord($this->area, 7, 3, 3);
		
		// When
		$record->updateResults(self::$conn, $result, $trial);
		
		// Then
		$this->expectCourseProgress($this->area, 7, 3, 4);
	}
	
	public function testUpdatingResultsFromFailedtoPassed()
	{
		// Given
		$record = CourseProgress::fetchByArea(self::$conn, $this->area);
		$result = $this->getTestResult(Question::RESULT_INVALID);
		$trial = $this->getTestTrial(Question::RESULT_INVALID);
		$this->fillRecord($this->area, 7, 3, 3);
		
		// When
		$record->updateResults(self::$conn, $result, $trial);
		
		// Then
		$this->expectCourseProgress($this->area, 7, 3, 3);
	}
	
	public function testUpdatingResultsFromFailedtoFailed()
	{
		// Given
		$record = CourseProgress::fetchByArea(self::$conn, $this->area);
		$result = $this->getTestResult(Question::RESULT_INVALID);
		$trial = $this->getTestTrial(Question::RESULT_CORRECT);
		$this->fillRecord($this->area, 7, 3, 3);
		
		// When
		$record->updateResults(self::$conn, $result, $trial);
		
		// Then
		$this->expectCourseProgress($this->area, 7, 4, 2);
	}
	
	private function getTestResult($initial) {
		$result = $this->getMockBuilder(TestResult::class)->getMock();
		$result->method('getResult')->will($this->returnValue($initial));
		return $result;
	}
	
	private function getTestTrial($initial) {
		$result = $this->getMockBuilder(TestTrial::class)->getMock();
		$result->method('getResult')->will($this->returnValue($initial));
		return $result;
	}
	
	private function fillRecord(Area $area, $mandatory, $passed, $failed)
	{
		self::$conn->update(CourseTables::COURSE_PROGRESS_TBL, array(
			'mandatoryCourseNum' => $mandatory,
			'passedCourseNum' =>$passed,
			'failedCourseNum' => $failed
		), array('areaId' => $area->getId()));
	}
	
	private function expectCourseProgress(Area $area, $mandatory, $passed, $failed)
	{
		$this->assertFieldEqualsEx(CourseTables::COURSE_PROGRESS_TBL, 'areaId', $area->getId(), 'mandatoryCourseNum', $mandatory);
		$this->assertFieldEqualsEx(CourseTables::COURSE_PROGRESS_TBL, 'areaId', $area->getId(), 'passedCourseNum', $passed);
		$this->assertFieldEqualsEx(CourseTables::COURSE_PROGRESS_TBL, 'areaId', $area->getId(), 'failedCourseNum', $failed);
	}
}
