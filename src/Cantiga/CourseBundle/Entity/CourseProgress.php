<?php
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
	/*
	public static function fromArray(Area $area, array $data, $prefix = '') {
		$record = new CourseRecord($area);
		$record->mandatoryCourseNum = $data[$prefix.'mandatoryCourseNum'];
		$record->passedCourseNum = $data[$prefix.'passedCourseNum'];
		$record->failedCourseNum = $data[$prefix.'failedCourseNum'];
		
		return $record;
	}
	*/

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
	 * @param TestResult $result
	 * @param TestTrial $trial
	 */
	public function updateResults(Connection $conn, TestResult $result, TestTrial $trial)
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