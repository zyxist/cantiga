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
namespace Cantiga\CourseBundle\Entity;

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CourseBundle\CourseTables;
use Cantiga\CourseBundle\Exception\CourseTestException;
use Cantiga\Metamodel\DataMappers;
use Doctrine\DBAL\Connection;
use Exception;

/**
 * Represents a result of the test solved by the user, and controls the time limits
 * required to solve it.
 *
 * @author Tomasz Jędrzejewski
 */
class TestResult {
	const TIME_BETWEEN_TRIALS = 'P1D';
	const TIME_BETWEEN_TRIALS_SEC = 86400;
	
	/**
	 * @var Area
	 */
	private $area;
	/**
	 * @var Course
	 */
	private $course;
	private $trialNumber;
	private $startedAt;
	private $completedAt;
	private $result;
	private $totalQuestions;
	private $passedQuestions;
	
	public static function fetchResult(Connection $conn, Area $area, Course $course)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.CourseTables::COURSE_RESULT_TBL.'` WHERE `areaId` = :areaId AND `courseId` = :courseId', 
			array(':areaId' => $area->getId(), ':courseId' => $course->getId())
		);
		$result = new TestResult();
		$result->area = $area;
		$result->course = $course;
		$result->result = Question::RESULT_UNKNOWN;
		if (false === $data) {
			return $result;
		}
		DataMappers::fromArray($result, $data);
		return $result;
	}
	
	/**
	 * @return Area
	 */
	public function getArea()
	{
		return $this->area;
	}

	/**
	 * @return Course
	 */
	public function getCourse()
	{
		return $this->course;
	}

	public function getTrialNumber()
	{
		return $this->trialNumber;
	}
	
	public function getStartedAt()
	{
		return $this->startedAt;
	}

	public function getCompletedAt()
	{
		return $this->completedAt;
	}

	public function getResult()
	{
		return $this->result;
	}

	public function getTotalQuestions()
	{
		return $this->totalQuestions;
	}

	public function getPassedQuestions()
	{
		return $this->passedQuestions;
	}

	public function getPercentageResult()
	{
		return round($this->passedQuestions / $this->totalQuestions * 100.0);
	}
	
	public function isSolved()
	{
		return $this->result != Question::RESULT_UNKNOWN;
	}
	
	/**
	 * Whether the test can be repeated again.
	 * @return boolean
	 */
	public function canBeStarted()
	{
		if(!$this->course->deadlineNotReached()) {
			return false;
		}
		if($this->result == Question::RESULT_UNKNOWN) {
			return true;
		}
		if($this->result == Question::RESULT_INVALID) {
			if($this->startedAt < (time() - self::TIME_BETWEEN_TRIALS_SEC)) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Starts a new trial for solving the test. It verifies the time limits and throws an exception,
	 * if any of them is not matched.
	 * 
	 * @throws CourseTestException
	 */
	public function startNewTrial(Connection $conn)
	{
		if($this->refresh($conn)) {
			if($this->result == Question::RESULT_CORRECT) {
				throw new CourseTestException('Cannot start a passed test.');
			}
			if($this->startedAt > (time() - self::TIME_BETWEEN_TRIALS_SEC)) {
				throw new CourseTestException('Please wait at least 24 hours before starting another trial.');
			}
			$this->startedAt = time();
		}
		$this->save($conn);
	}
	
	/**
	 * Completes solving the test and saves the results to the database.
	 * 
	 * @param Connection $conn
	 * @param TestTrial $trial
	 * @throws Exception
	 */
	public function completeTrial(Connection $conn, TestTrial $trial)
	{
		$this->refresh($conn);
			
		$limit = $this->startedAt->getTimestamp();
		$limit += ($trial->getTimeLimitInMinutes() * 60);
		
		if($limit < time()) {
			throw new CourseTestException('The time to solve this test has passed, sorry.');
		}
			
		$this->area->getCourseProgress()->updateResults($this, $trial);
			
		$this->result = $trial->getResult();
		$this->totalQuestions = $trial->getQuestionNumber();
		$this->passedQuestions = $trial->countPassedQuestions();
		$this->completedAt = time();
		$this->save($conn);
	}
	
	protected function refresh(Connection $conn)
	{
		$currentSet = $conn->fetchAssoc('SELECT `result`, `trialNumber`, `startedAt`, `completedAt`, `totalQuestions`, `passedQuestions` FROM `'.CourseTables::COURSE_RESULT_TBL.'` WHERE `areaId` = :areaId AND `courseId` = :courseId', array(
			':areaId' => $this->area->getId(),
			':courseId' => $this->course->getId()
		));
		if(empty($currentSet)) {
			$this->trialNumber = 1;
			$this->startedAt = time();
			$this->completedAt = null;
			$this->result = Question::RESULT_UNKNOWN;
			$this->totalQuestions = 0;
			$this->passedQuestions = 0;
			return false;
		} else {
			$this->trialNumber = $currentSet['trialNumber'];
			$this->startedAt = $currentSet['startedAt'];
			$this->completedAt = $currentSet['completedAt'];
			$this->result = $currentSet['result'];
			$this->totalQuestions = $currentSet['totalQuestions'];
			$this->passedQuestions = $currentSet['passedQuestions'];
			return true;
		}
	}
	
	protected function save(Connection $conn)
	{		
		$stmt = $conn->prepare('INSERT INTO `'.CourseTables::COURSE_RESULT_TBL.'` '
			. '(`areaId`, `courseId`, `trialNumber`, `startedAt`, `completedAt`, `result`, `totalQuestions`, `passedQuestions`) '
			. 'VALUES(:areaId, :courseId, :trialNum, :startedAt, :completedAt, :result, :totalQuestions, :passedQuestions) '
			. 'ON DUPLICATE KEY UPDATE `trialNumber` = VALUES(`trialNumber`), `startedAt` = VALUES(`startedAt`), '
			. '`completedAt` = VALUES(`completedAt`), `result` = VALUES(`result`), `totalQuestions` = VALUES(`totalQuestions`), '
			. '`passedQuestions` = VALUES(`passedQuestions`)');
		$stmt->bindValue(':areaId', $this->area->getId());
		$stmt->bindValue(':courseId', $this->course->getId());
		$stmt->bindValue(':trialNum', $this->getTrialNumber());
		$stmt->bindValue(':result', $this->getResult());
		$stmt->bindValue(':startedAt', $this->getStartedAt()->getTimestamp());
		$stmt->bindValue(':completedAt', $this->getCompletedAt() === null ? null : $this->getCompletedAt()->getTimestamp());
		$stmt->bindValue(':totalQuestions', $this->getTotalQuestions());
		$stmt->bindValue(':passedQuestions', $this->getPassedQuestions());
		$stmt->execute();
	}
}
