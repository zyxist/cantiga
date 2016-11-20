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
use Cantiga\CoreBundle\Entity\User;
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
class TestResult extends AbstractTestResult {
	const TIME_BETWEEN_TRIALS = 'P1D';
	const TIME_BETWEEN_TRIALS_SEC = 86400;
	
	/**
	 * @var User
	 */
	private $user;
	/**
	 * @var Course
	 */
	private $course;
	
	public static function fetchResult(Connection $conn, User $user, Course $course)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.CourseTables::COURSE_RESULT_TBL.'` WHERE `userId` = :userId AND `courseId` = :courseId', 
			array(':userId' => $user->getId(), ':courseId' => $course->getId())
		);
		$result = new TestResult();
		$result->user = $user;
		$result->course = $course;
		$result->result = Question::RESULT_UNKNOWN;
		if (false === $data) {
			return $result;
		}
		DataMappers::fromArray($result, $data);
		return $result;
	}
	
	/**
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @return Course
	 */
	public function getCourse()
	{
		return $this->course;
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
	 * @param $conn Database connection
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
	 * Completes solving the test and saves the results to the database. If this is the first passed trial
	 * among all of the members of the given area, it the result is saved as the result for the entire area, too.
	 * 
	 * @param Connection $conn
	 * @param Area $area Area the user taking the trial is a member of
	 * @param TestTrial $trial
	 * @return CourseProgress|boolean
	 * @throws Exception
	 */
	public function completeTrial(Connection $conn, Area $area, TestTrial $trial)
	{
		$this->refresh($conn);
			
		$limit = $this->startedAt;
		$limit += ($trial->getTimeLimitInMinutes() * 60);
		
		if($limit < time()) {
			throw new CourseTestException('TestTimeHasPassedMsg');
		}
			
		$this->result = $trial->getResult();
		$this->totalQuestions = $trial->getQuestionNumber();
		$this->passedQuestions = $trial->countPassedQuestions();
		$this->completedAt = time();
		$this->save($conn);
		return $this->tryRecordingAreaResult($conn, $area, $trial);
	}
	
	protected function refresh(Connection $conn)
	{
		$currentSet = $conn->fetchAssoc('SELECT `result`, `trialNumber`, `startedAt`, `completedAt`, `totalQuestions`, `passedQuestions` FROM `'.CourseTables::COURSE_RESULT_TBL.'` WHERE `userId` = :userId AND `courseId` = :courseId', array(
			':userId' => $this->user->getId(),
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
			. '(`userId`, `courseId`, `trialNumber`, `startedAt`, `completedAt`, `result`, `totalQuestions`, `passedQuestions`) '
			. 'VALUES(:userId, :courseId, :trialNum, :startedAt, :completedAt, :result, :totalQuestions, :passedQuestions) '
			. 'ON DUPLICATE KEY UPDATE `trialNumber` = VALUES(`trialNumber`), `startedAt` = VALUES(`startedAt`), '
			. '`completedAt` = VALUES(`completedAt`), `result` = VALUES(`result`), `totalQuestions` = VALUES(`totalQuestions`), '
			. '`passedQuestions` = VALUES(`passedQuestions`)');
		$stmt->bindValue(':userId', $this->user->getId());
		$stmt->bindValue(':courseId', $this->course->getId());
		$stmt->bindValue(':trialNum', $this->getTrialNumber());
		$stmt->bindValue(':result', $this->getResult());
		$stmt->bindValue(':startedAt', $this->getStartedAt());
		$stmt->bindValue(':completedAt', $this->getCompletedAt());
		$stmt->bindValue(':totalQuestions', $this->getTotalQuestions());
		$stmt->bindValue(':passedQuestions', $this->getPassedQuestions());
		$stmt->execute();
	}
	
	protected function tryRecordingAreaResult(Connection $conn, Area $area, TestTrial $trial)
	{
		$areaResult = AreaCourseResult::fetchResult($conn, $area, $this->course);
		if ($areaResult->result == Question::RESULT_UNKNOWN) {
			$conn->insert(CourseTables::COURSE_AREA_RESULT_TBL, [
				'areaId' => $area->getId(),
				'userId' => $this->user->getId(),
				'courseId' => $this->course->getId()
			]);
			$progress = CourseProgress::fetchByArea($conn, $area);
			$progress->updateResults($conn, $areaResult, $trial);
			return $progress;
		} elseif ($areaResult->result == Question::RESULT_INVALID) {
			$conn->update(CourseTables::COURSE_AREA_RESULT_TBL, [
				'areaId' => $area->getId(),
				'userId' => $this->user->getId(),
				'courseId' => $this->course->getId()
			], [
				'areaId' => $area->getId(),
				'courseId' => $this->course->getId()
			]);
			$progress = CourseProgress::fetchByArea($conn, $area);
			$progress->updateResults($conn, $areaResult, $trial);
			return $progress;
		}
		return true;
	}
	
	public static function processResults(array &$array, $prefix = '')
	{
		if(!empty($array[$prefix.'totalQuestions'])) {
			$array[$prefix.'score'] = round((int) $array[$prefix.'passedQuestions'] / (float) $array[$prefix.'totalQuestions'] * 100.0);
		}
		if(empty($array[$prefix.'result'])) { 
			$array[$prefix.'result'] = Question::RESULT_UNKNOWN;
		}
		return $array;
	}
}
