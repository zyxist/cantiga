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
use Cantiga\CoreBundle\Entity\Language;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\Territory;
use Cantiga\CoreBundle\Tests\Utils\DatabaseTestCase;
use Cantiga\CourseBundle\CourseTables;
use Cantiga\CourseBundle\Entity\Course;
use Cantiga\CourseBundle\Entity\CourseProgress;
use Cantiga\CourseBundle\Entity\Question;
use Cantiga\CoreBundle\Entity\User;

class CourseTest extends DatabaseTestCase {
	private $project;
	private $territory;
	private $status;
	
	private $area;
	private $area2;
	
	private $user;
	private $user2;
	
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
		$this->area2 = Area::newArea($this->project, $this->territory, $this->status, 'Area2');
		
		$lang = new Language();
		$lang->setId(1);
		
		$this->user = User::newUser('login', 'Some user', $lang);
		$this->user->insert(self::$conn);
		
		$this->user2 = User::newUser('login2', 'Another user', $lang);
		$this->user2->insert(self::$conn);
		
		$tpa1 = new CourseProgress($this->area);
		$tpa1->insert(self::$conn);
	}
	
	public function tearDown()
	{
		self::$conn->executeUpdate('DELETE FROM `'.CourseTables::COURSE_RESULT_TBL.'`');
		self::$conn->executeUpdate('DELETE FROM `'.CoreTables::AREA_TBL.'`');
		self::$conn->executeUpdate('DELETE FROM `'.CoreTables::USER_TBL.'`');
		self::$conn->executeUpdate('DELETE FROM `'.CourseTables::COURSE_TBL.'`');
	}
	
	public function testCreatingPublishedCourse()
	{
		// Given
		$course = new Course();
		$course->setName('Foo');
		$this->setDefaults($course);
		$course->setIsPublished(true);
		
		$this->fillRecord($this->area, 3, 2, 1);
		
		// When
		$course->insert(self::$conn);
		
		// Then
		$this->assertExists(CourseTables::COURSE_TBL, $course->getId());
		$this->expectCourseProgress($this->area, 4, 2, 1);
	}
	
	public function testCreatingUnpublishedCourse()
	{
		// Given
		$course = new Course();
		$course->setName('Foo');
		$this->setDefaults($course);
		$course->setIsPublished(false);
		
		$this->fillRecord($this->area, 3, 2, 1);
		
		// When
		$course->insert(self::$conn);
		
		// Then
		$this->assertExists(CourseTables::COURSE_TBL, $course->getId());
		$this->expectCourseProgress($this->area, 3, 2, 1);
	}
	
	public function testSwitchingFromPublishedToUnpublished()
	{
		// Given
		$this->area2->insert(self::$conn);
		$tpa2 = new CourseProgress($this->area2);
		$tpa2->insert(self::$conn);
		
		$course = new Course();
		$course->setName('Foo');
		$this->setDefaults($course);
		$course->setIsPublished(true);
		$course->insert(self::$conn);
		
		$this->insertResult($course, $this->area, $this->user, Question::RESULT_CORRECT);
		$this->insertResult($course, $this->area2, $this->user2, Question::RESULT_INVALID);
		
		$this->fillRecord($this->area, 3, 2, 1);
		$this->fillRecord($this->area2, 3, 2, 1);
		
		// When
		$course->setIsPublished(false);
		$course->update(self::$conn);
		
		// Then
		$this->assertExists(CourseTables::COURSE_TBL, $course->getId());
		$this->expectCourseProgress($this->area, 2, 1, 1);
		$this->expectCourseProgress($this->area2, 2, 2, 0);
	}
	
	public function testSwitchingFromUnpublishedToPublished()
	{
		// Given
		$this->area2->insert(self::$conn);
		$tpa2 = new CourseProgress($this->area2);
		$tpa2->insert(self::$conn);
		
		$course = new Course();
		$course->setName('Foo');
		$this->setDefaults($course);
		$course->setIsPublished(false);
		$course->insert(self::$conn);
		
		$this->insertResult($course, $this->area, $this->user, Question::RESULT_CORRECT);
		$this->insertResult($course, $this->area2, $this->user2, Question::RESULT_INVALID);
		
		$this->fillRecord($this->area, 3, 2, 1);
		$this->fillRecord($this->area2, 3, 2, 1);
		
		// When
		$course->setIsPublished(true);
		$course->update(self::$conn);
		
		// Then
		$this->assertExists(CourseTables::COURSE_TBL, $course->getId());
		$this->expectCourseProgress($this->area, 4, 3, 1);
		$this->expectCourseProgress($this->area2, 4, 2, 2);
	}
	
	public function testDeletingPublishedCourse()
	{
		// Given
		$this->area2->insert(self::$conn);
		$tpa2 = new CourseProgress($this->area2);
		$tpa2->insert(self::$conn);
		
		$course = new Course();
		$course->setName('Foo');
		$this->setDefaults($course);
		$course->setIsPublished(true);
		$course->insert(self::$conn);
		
		$this->insertResult($course, $this->area, $this->user, Question::RESULT_CORRECT);
		$this->insertResult($course, $this->area2, $this->user2, Question::RESULT_INVALID);
		
		$this->fillRecord($this->area, 3, 2, 1);
		$this->fillRecord($this->area2, 3, 2, 1);
		
		// When
		$course->remove(self::$conn);
		
		// Then
		$this->assertNotExists(CourseTables::COURSE_TBL, $course->getId());
		$this->expectCourseProgress($this->area, 2, 1, 1);
		$this->expectCourseProgress($this->area2, 2, 2, 0);
	}
	
	public function testDeletingUnpublishedCourse()
	{
		// Given
		$this->area2->insert(self::$conn);
		$tpa2 = new CourseProgress($this->area2);
		$tpa2->insert(self::$conn);
		
		$course = new Course();
		$course->setName('Foo');
		$this->setDefaults($course);
		$course->setIsPublished(false);
		$course->insert(self::$conn);
		
		$this->insertResult($course, $this->area, $this->user, Question::RESULT_CORRECT);
		$this->insertResult($course, $this->area2, $this->user2, Question::RESULT_INVALID);
		
		$this->fillRecord($this->area, 3, 2, 1);
		$this->fillRecord($this->area2, 3, 2, 1);
		
		// When
		$course->remove(self::$conn);
		
		// Then
		$this->assertNotExists(CourseTables::COURSE_TBL, $course->getId());
		$this->expectCourseProgress($this->area, 3, 2, 1);
		$this->expectCourseProgress($this->area2, 3, 2, 1);
	}
	
	private function setDefaults(Course $course)
	{
		$course->setProject($this->project);
		$course->setAuthorName('Johnny B.');
		$course->setAuthorEmail('johnny-b@example.com');
		$course->setPresentationLink('http://www.example.com/');
		$course->setDisplayOrder(1);
		$course->setDescription('foo');
		$course->setNotes('foo');
	}
	
	private function insertResult(Course $course, Area $area, User $user, $result)
	{
		self::$conn->insert(CourseTables::COURSE_RESULT_TBL, array(
			'userId' => $user->getId(),
			'courseId' => $course->getId(),
			'trialNumber' => 1,
			'startedAt' => time(),
			'completedAt' => time(),
			'result' => $result,
			'totalQuestions' => 0,
			'passedQuestions' => 0
		));
		self::$conn->insert(CourseTables::COURSE_AREA_RESULT_TBL, [
			'areaId' => $area->getId(),
			'userId' => $user->getId(),
			'courseId' => $course->getId()
		]);
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
