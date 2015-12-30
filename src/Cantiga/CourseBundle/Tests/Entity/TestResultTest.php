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
use Cantiga\CourseBundle\CourseTables;
use Cantiga\CourseBundle\Entity\Course;
use Cantiga\CourseBundle\Entity\CourseProgress;
use Cantiga\CourseBundle\Entity\Question;
use Cantiga\CourseBundle\Entity\TestResult;
use Cantiga\CourseBundle\Entity\TestTrial;
use Cantiga\CourseBundle\Exception\CourseTestException;

class TestResultTest extends DatabaseTestCase
{
	private $project;
	private $status;
	private $territory;
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
		
		$this->course = new Course();
		$this->course->setProject($this->project);
		$this->course->setName('Foo');
		$this->course->setAuthorName('Foo');
		$this->course->setAuthorEmail('foo@example.com');
		$this->course->setPresentationLink('http://www.example.com/');
		$this->course->setIsPublished(true);
		$this->course->insert(self::$conn);
		
		$pp = new CourseProgress($this->area);
		$pp->insert(self::$conn);
	}
	
	public function tearDown()
	{
		self::$conn->executeUpdate('DELETE FROM `'.CourseTables::COURSE_RESULT_TBL.'`');
		self::$conn->executeUpdate('DELETE FROM `'.CoreTables::AREA_TBL.'`');
		self::$conn->executeUpdate('DELETE FROM `'.CourseTables::COURSE_TBL.'`');
	}
	
	public function testSuccessfulTestCompletion()
	{
		// Given
		$testResult = TestResult::fetchResult(self::$conn, $this->area, $this->course);
		$testResult->setStartedAt(time());
		$trial = $this->getTestTrial(Question::RESULT_CORRECT, 12, 11, 10);
		$this->fillRecord($this->area, 1, 0, 0);
		
		// When
		$testResult->completeTrial(self::$conn, $trial);
		
		// Then
		$this->assertEquals(Question::RESULT_CORRECT, $testResult->getResult());
		$this->assertEquals(12, $testResult->getTotalQuestions());
		$this->assertEquals(11, $testResult->getPassedQuestions());
		$this->expectCourseProgress($this->area, 1, 1, 0);
	}
	
	public function testFailingTestCompletion()
	{
		// Given
		$testResult = TestResult::fetchResult(self::$conn, $this->area, $this->course);
		$testResult->setStartedAt(time());
		$trial = $this->getTestTrial(Question::RESULT_INVALID, 12, 4, 10);
		$this->fillRecord($this->area, 1, 0, 0);
		
		// When
		$testResult->completeTrial(self::$conn, $trial);
		
		// Then
		$this->assertEquals(Question::RESULT_INVALID, $testResult->getResult());
		$this->assertEquals(12, $testResult->getTotalQuestions());
		$this->assertEquals(4, $testResult->getPassedQuestions());
		$this->expectCourseProgress($this->area, 1, 0, 1);
	}
	
	public function testTimeHasPassed()
	{
		// Given
		$timeLimit = 5;
		
		$testResult = TestResult::fetchResult(self::$conn, $this->area, $this->course);
		$testResult->startNewTrial(self::$conn);
		self::$conn->update(CourseTables::COURSE_RESULT_TBL,
			['startedAt' => time() - 2 * $timeLimit * 60],
			['areaId' => $this->area->getId(), 'courseId' => $this->course->getId()]
		);
		$trial = $this->getTestTrial(Question::RESULT_INVALID, 12, 4, $timeLimit);
		$this->fillRecord($this->area, 1, 0, 0);
		
		try {
			// When
			$testResult->completeTrial(self::$conn, $trial);
			$this->fail('Exception not thrown');
		} catch (CourseTestException $exception) {
			// Then
			$this->assertEquals('TestTimeHasPassedMsg', $exception->getMessage());
			$this->expectCourseProgress($this->area, 1, 0, 0);
		}		
	}
	
	private function getTestTrial($result, $questionNum, $passedQuestions, $timeLimit) {
		$item = $this->getMockBuilder(TestTrial::class)->getMock();
		$item->method('getResult')->will($this->returnValue($result));
		$item->method('getQuestionNumber')->will($this->returnValue($questionNum));
		$item->method('countPassedQuestions')->will($this->returnValue($passedQuestions));
		$item->method('getTimeLimitInMinutes')->will($this->returnValue($timeLimit));
		return $item;
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
