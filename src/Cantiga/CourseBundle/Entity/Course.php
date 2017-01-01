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

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\CourseBundle\CourseTables;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\Exception\ModelException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use LogicException;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Represents a single, on-line course, with a set of test questions.
 * Note that this class has been ported from the old code base, so it
 * does not do certain things in 'Cantiga' way.
 */
class Course implements InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	private $id;
	private $project;
	private $name;
	private $description;
	private $authorName;
	private $authorEmail;
	private $lastUpdated;
	private $presentationLink;
	private $deadline;
	private $isPublished = false;
	private $displayOrder = 1;
	private $notes = '';
	/**
	 *
	 * @var CourseTest
	 */
	private $test;
	
	public static function fetchByProject(Connection $conn, $id, Project $project)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.CourseTables::COURSE_TBL.'` WHERE `id` = :id AND `projectId` = :projectId', [':id' => $id, ':projectId' => $project->getId()]);
		if (empty($data)) {
			return false;
		}
		$test = $conn->fetchAssoc('SELECT * FROM `'.CourseTables::COURSE_TEST_TBL.'` WHERE `courseId` = :id', [':id' => $id]);
		
		$item = self::fromArray($data);
		$item->project = $project;
		if (false !== $test) {
			$item->test = new CourseTest($item, $test['testStructure']);
		}
		return $item;
	}
	
	public static function fetchPublished(Connection $conn, $id, Project $project)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.CourseTables::COURSE_TBL.'` WHERE `id` = :id AND `projectId` = :projectId AND `isPublished` = 1', [':id' => $id, ':projectId' => $project->getId()]);
		if (empty($data)) {
			return false;
		}
		
		$item = self::fromArray($data);
		$item->project = $project;
		$test = $conn->fetchAssoc('SELECT * FROM `'.CourseTables::COURSE_TEST_TBL.'` WHERE `courseId` = :id', [':id' => $id]);
		if (false !== $test) {
			$item->test = new CourseTest($item, $test['testStructure']);
		}
		return $item;
	}
	
	public static function fromArray($array, $prefix = '')
	{
		$item = new Course;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public static function getRelationships()
	{
		return ['project'];
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('name', new NotBlank());
		$metadata->addPropertyConstraint('name', new Length(array('min' => 2, 'max' => 50)));
		$metadata->addPropertyConstraint('description', new Length(array('min' => 2, 'max' => 255)));
		$metadata->addPropertyConstraint('authorName', new NotBlank());
		$metadata->addPropertyConstraint('authorName', new Length(array('min' => 2, 'max' => 50)));
		$metadata->addPropertyConstraint('authorEmail', new NotBlank());
		$metadata->addPropertyConstraint('authorEmail', new Length(array('min' => 2, 'max' => 100)));
		$metadata->addPropertyConstraint('authorEmail', new Email());
		$metadata->addPropertyConstraint('presentationLink', new NotBlank());
		$metadata->addPropertyConstraint('presentationLink', new Url());
		$metadata->addPropertyConstraint('displayOrder', new NotBlank());
		$metadata->addPropertyConstraint('displayOrder', new Range(array('min' => 0, 'max' => 100)));
		$metadata->addPropertyConstraint('notes', new Length(array('min' => 0, 'max' => 255)));
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getProject()
	{
		return $this->project;
	}

	public function getName() 
	{
		return $this->name;
	}

	public function getAuthorName()
	{
		return $this->authorName;
	}

	public function getAuthorEmail() 
	{
		return $this->authorEmail;
	}

	public function getLastUpdated() 
	{
		return $this->lastUpdated;
	}

	public function getPresentationLink()
	{
		return $this->presentationLink;
	}

	public function getDeadline()
	{
		return $this->deadline;
	}

	public function getIsPublished()
	{
		return $this->isPublished;
	}
	
	public function getPublished()
	{
		return $this->isPublished;
	}

	public function getDisplayOrder()
	{
		return $this->displayOrder;
	}

	public function getNotes()
	{
		return $this->notes;
	}

	public function setId($id)
	{
		DataMappers::noOverwritingId($this->id);
		$this->id = $id;
		return $this;
	}
	
	public function setProject(Project $project)
	{
		DataMappers::noOverwritingField($this->project);
		$this->project = $project;
		return $this;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function setAuthorName($authorName)
	{
		$this->authorName = $authorName;
		return $this;
	}

	public function setAuthorEmail($authorEmail)
	{
		$this->authorEmail = $authorEmail;
		return $this;
	}

	public function setPresentationLink($presentationLink)
	{
		$this->presentationLink = $presentationLink;
		return $this;
	}
	
	public function setLastUpdated($value)
	{
		DataMappers::noOverwritingField($this->lastUpdated);
		$this->lastUpdated = (int) $value;
		return $this;
	}

	public function setDeadline($deadline)
	{
		$this->deadline = $deadline;
		return $this;
	}

	public function setIsPublished($published)
	{
		$this->isPublished = (bool) $published;
		return $this;
	}

	public function setDisplayOrder($displayOrder)
	{
		$this->displayOrder = (int) $displayOrder;
		return $this;
	}

	public function setNotes($notes)
	{
		$this->notes = $notes;
		return $this;
	}
	
	public function getDescription()
	{
		return $this->description;
	}

	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}
	
	/**
	 * Checks whether the deadline has not been reached.
	 * 
	 * @return boolean
	 */
	public function deadlineNotReached()
	{
		if($this->deadline === null) {
			return true;
		}
		return (($this->deadline + 86400) > time());
	}
	
	/**
	 * Checks whether test questions have been generated for this course.
	 * 
	 * @return boolean
	 */
	public function hasTest()
	{
		return $this->test !== null;
	}
	
	/**
	 * Fetches an entity that represents a test question set. Note that it must be persisted
	 * separately.
	 * 
	 * @return CourseTest
	 */
	public function getTest()
	{
		return $this->test;
	}

	/**
	 * Creates a new set of test questions for the test. It must be saved separately with
	 * <tt>getTest()->save()</tt> call.
	 * 
	 * @param string $content XML with the questions
	 */
	public function createTest($content)
	{
		$this->test = new CourseTest($this, $content);
		return $this;
	}
	
	/**
	 * Certain courses do not have a test. In this situation the user may click a button where he
	 * simply confirms in good-faith that he/she has completed the given course.
	 * 
	 * @param Connection $conn Database connection
	 * @param Area $area The area which finishes the course.
	 * @param User $user The user who completes the course.
	 * @return CourseProgress|boolean
	 */
	public function confirmGoodFaithCompletion(Connection $conn, Area $area, User $user)
	{
		if($this->hasTest()) {
			throw new ModelException('Cannot confirm good-faith completion for a course that has a test assigned.');
		}
		try {
			$stmt = $conn->prepare('INSERT INTO `'.CourseTables::COURSE_RESULT_TBL.'` '
				. '(`userId`, `courseId`, `trialNumber`, `startedAt`, `completedAt`, `result`, `totalQuestions`, `passedQuestions`) '
				. 'VALUES(:userId, :courseId, 1, :startedAt, :completedAt, :result, :totalQuestions, :passedQuestions)');
			$stmt->bindValue(':userId', $user->getId());
			$stmt->bindValue(':courseId', $this->getId());
			$stmt->bindValue(':result', Question::RESULT_CORRECT);
			$stmt->bindValue(':startedAt', time());
			$stmt->bindValue(':completedAt', time());
			$stmt->bindValue(':totalQuestions', 1);
			$stmt->bindValue(':passedQuestions', 1);
			$stmt->execute();
			
			$areaResult = AreaCourseResult::fetchResult($conn, $area, $this, true);
			if ($areaResult->getResult() == Question::RESULT_UNKNOWN) {
				$conn->insert(CourseTables::COURSE_AREA_RESULT_TBL, [
					'userId' => $user->getId(),
					'areaId' => $area->getId(),
					'courseId' => $this->id
				]);
				$progress = CourseProgress::fetchByArea($conn, $area, true);
				$progress->updateGoodFaithCompletion($conn);
				return $progress;
			}
			return true;
		} catch(UniqueConstraintViolationException $exception) {
			throw new ModelException('Cannot complete a completed test!');
		}
	}
	
	public function insert(Connection $conn)
	{
		if(null !== $this->getId()) {
			throw new LogicException('Cannot perform insert() on a persisted Course instance.');
		}
		$this->lastUpdated = time();
		$conn->insert(
			CourseTables::COURSE_TBL,
			DataMappers::pick($this, ['name', 'description', 'project', 'authorName', 'authorEmail', 'lastUpdated', 'presentationLink', 'deadline', 'isPublished', 'displayOrder', 'notes'])
		);
		$this->setId($conn->lastInsertId());

		if($this->getPublished()) {
			$this->incrementMandatoryCourses($conn);
		}
		return $this->getId();
	}

	public function update(Connection $conn)
	{
		if(null === $this->getId()) {
			throw new LogicException('Cannot perform update() on an unpersisted Course instance.');
		}
		$oldPublished = (boolean) $conn->fetchColumn('SELECT `isPublished` FROM `'.CourseTables::COURSE_TBL.'` WHERE `id` = :id FOR UPDATE', array(':id' => $this->getId()));

		$stmt = $conn->prepare('UPDATE `'.CourseTables::COURSE_TBL.'` SET '
			. '`name` = :name,'
			. '`description` = :description,'
			. '`authorName` = :authorName,'
			. '`authorEmail` = :authorEmail,'
			. '`lastUpdated` = :lastUpdated,'
			. '`presentationLink` = :presentationLink,'
			. '`deadline` = :deadline,'
			. '`isPublished` = :isPublished,'
			. '`displayOrder` = :displayOrder,'
			. '`notes` = :notes WHERE `id` = :id');
		$stmt->bindValue(':id', $this->getId());
		$stmt->bindValue(':name', $this->getName());
		$stmt->bindValue(':description', $this->getDescription());
		$stmt->bindValue(':authorName', $this->getAuthorName());
		$stmt->bindValue(':authorEmail', $this->getAuthorEmail());
		$stmt->bindValue(':lastUpdated', time());
		$stmt->bindValue(':presentationLink', $this->getPresentationLink());
		if(null !== $this->deadline) {
			$stmt->bindValue(':deadline', $this->getDeadline());
		} else {
			$stmt->bindValue(':deadline', null);
		}
		$stmt->bindValue(':isPublished', (int) $this->getPublished());
		$stmt->bindValue(':displayOrder', (int) $this->getDisplayOrder());
		$stmt->bindValue(':notes', $this->getNotes());
		$stmt->execute();

		if($oldPublished !== $this->isPublished) {
			if($this->isPublished) {
				$this->incrementMandatoryCourses($conn);
				$this->revokeCourseFromAreaRecords($conn);
			} else {
				$this->decrementMandatoryCourses($conn);
				$this->cancelCourseFromAreaRecords($conn);
			}
		}
	}
	
	public function canRemove()
	{
		return true;
	}
	
	public function remove(Connection $conn)
	{
		if(null === $this->getId()) {
			throw new LogicException('Cannot perform remove() on an unpersisted Course instance.');
		}		
		$this->refresh($conn);
		if($this->isPublished) {
			$this->decrementMandatoryCourses($conn);
			$this->cancelCourseFromAreaRecords($conn);
		}

		$conn->executeUpdate('DELETE FROM `'.CourseTables::COURSE_TBL.'` WHERE `id` = :id', array(':id' => $this->getId()));
	}
	
	/**
	 * TODO: move out of the entity to a separate renderer.
	 * 
	 * @return string
	 */
	public function generatePresentationCode()
	{
		if(preg_match('/docs.google.com\/presentation\/d\/([a-zA-Z0-9\-\_]+)\/(.*)/', $this->presentationLink, $matches)) {
			return '<iframe src="http://docs.google.com/presentation/embed?id='.$matches[1].'&amp;start=false&amp;loop=false&amp;" frameborder="0" width="800" height="520" allowfullscreen="true"></iframe>';
		} else if(strpos($this->presentationLink, 'http://prezi.com/embed/') !== false) {
			return '<iframe frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true" width="800" height="520" src="'.$this->presentationLink.'"></iframe>';
		} else {
			return '<p>Unknown presentation format.</p>';
		}
	}
	
	private function incrementMandatoryCourses(Connection $conn)
	{
		$conn->executeQuery('UPDATE `'.CourseTables::COURSE_PROGRESS_TBL.'` SET `mandatoryCourseNum` = (`mandatoryCourseNum` + 1) WHERE `areaId` IN (SELECT `id` FROM `'.CoreTables::AREA_TBL.'` WHERE `projectId` = :projectId)', [
			':projectId' => $this->project->getId(),
		]);
	}
	
	private function decrementMandatoryCourses(Connection $conn)
	{
		$conn->executeQuery('UPDATE `'.CourseTables::COURSE_PROGRESS_TBL.'` SET `mandatoryCourseNum` = (`mandatoryCourseNum` - 1) WHERE `areaId` IN (SELECT `id` FROM `'.CoreTables::AREA_TBL.'` WHERE `projectId` = :projectId)', [
			':projectId' => $this->project->getId(),
		]);
	}
	
	private function cancelCourseFromAreaRecords(Connection $conn) {
		$conn->executeUpdate('UPDATE `'.CourseTables::COURSE_PROGRESS_TBL.'` r '
			. 'INNER JOIN `'.CourseTables::COURSE_AREA_RESULT_TBL.'` at ON at.`areaId` = r.`areaId` '
			. 'INNER JOIN `'.CourseTables::COURSE_RESULT_TBL.'` tt ON tt.`userId` = at.`userId` '
			. 'SET r.`passedCourseNum` = (`passedCourseNum` - 1)  WHERE tt.`result` = '.Question::RESULT_CORRECT.' AND at.`courseId` = :courseId',
			array(':courseId' => $this->getId()));
		$conn->executeUpdate('UPDATE `'.CourseTables::COURSE_PROGRESS_TBL.'` r '
			. 'INNER JOIN `'.CourseTables::COURSE_AREA_RESULT_TBL.'` at ON at.`areaId` = r.`areaId` '
			. 'INNER JOIN `'.CourseTables::COURSE_RESULT_TBL.'` tt ON tt.`userId` = at.`userId` '
			. 'SET r.`failedCourseNum` = (`failedCourseNum` - 1)  WHERE tt.`result` = '.Question::RESULT_INVALID.' AND at.`courseId` = :courseId',
			array(':courseId' => $this->getId()));
	}
	
	private function revokeCourseFromAreaRecords(Connection $conn)
	{
		$conn->executeUpdate('UPDATE `'.CourseTables::COURSE_PROGRESS_TBL.'` r '
			. 'INNER JOIN `'.CourseTables::COURSE_AREA_RESULT_TBL.'` at ON at.`areaId` = r.`areaId` '
			. 'INNER JOIN `'.CourseTables::COURSE_RESULT_TBL.'` tt ON tt.`userId` = at.`userId` '
			. 'SET r.`passedCourseNum` = (`passedCourseNum` + 1)  WHERE tt.`result` = '.Question::RESULT_CORRECT.' AND at.`courseId` = :courseId',
			array(':courseId' => $this->getId()));
		$conn->executeUpdate('UPDATE `'.CourseTables::COURSE_PROGRESS_TBL.'` r '
			. 'INNER JOIN `'.CourseTables::COURSE_AREA_RESULT_TBL.'` at ON at.`areaId` = r.`areaId` '
			. 'INNER JOIN `'.CourseTables::COURSE_RESULT_TBL.'` tt ON tt.`userId` = at.`userId` '
			. 'SET r.`failedCourseNum` = (`failedCourseNum` + 1)  WHERE tt.`result` = '.Question::RESULT_INVALID.' AND at.`courseId` = :courseId',
			array(':courseId' => $this->getId()));
	}
	
	private function refresh(Connection $conn)
	{
		$this->published = (boolean) $conn->fetchColumn('SELECT `isPublished` FROM `'.CourseTables::COURSE_TBL.'` WHERE `id` = :id', array(':id' => $this->getId()));
	}
}
