<?php
namespace Cantiga\TrainingBundle\Entity;

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\TrainingBundle\TrainingTables;
use Doctrine\DBAL\Connection;
use Exception;

/**
 * Information about training result summary for the given area.
 *
 * @author Tomasz Jędrzejewski
 */
class TrainingProgress implements InsertableEntityInterface
{
	private $area;
	private $mandatoryTrainingNum = 0;
	private $passedTrainingNum = 0;
	private $failedTrainingNum = 0;
	
	public static function fetchByArea(Connection $conn, Area $area)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.TrainingTables::TRAINING_PROGRESS_TBL.'` WHERE `areaId` = :areaId', [':areaId' => $area->getId()]);
		if (false === $data) {
			return false;
		}
		$item = new TrainingProgress($area);
		$item->mandatoryTrainingNum = $data['mandatoryTrainingNum'];
		$item->passedTrainingNum = $data['passedTrainingNum'];
		$item->failedTrainingNum = $data['failedTrainingNum'];
		return $item;
	}

	public function __construct(Area $area)
	{
		$this->area = $area;
	}
	/*
	public static function fromArray(Area $area, array $data, $prefix = '') {
		$record = new TrainingRecord($area);
		$record->mandatoryTrainingNum = $data[$prefix.'mandatoryTrainingNum'];
		$record->passedTrainingNum = $data[$prefix.'passedTrainingNum'];
		$record->failedTrainingNum = $data[$prefix.'failedTrainingNum'];
		
		return $record;
	}
	*/

	public function insert(Connection $conn)
	{
		$this->mandatoryTrainingNum = $conn->fetchColumn('SELECT COUNT(`id`) FROM `'.TrainingTables::TRAINING_TBL.'` WHERE `isPublished` = 1');
			
		$conn->insert(TrainingTables::TRAINING_PROGRESS_TBL, array(
			'areaId' => $this->area->getId(),
			'mandatoryTrainingNum' => $this->mandatoryTrainingNum,
			'passedTrainingNum' => 0,
			'failedTrainingNum' => 0
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
			$this->passedTrainingNum++;
		} elseif($trial->getResult() == Question::RESULT_INVALID) {
			$this->failedTrainingNum++;
		}
			
		if($result->getResult() == Question::RESULT_INVALID) {
			$this->failedTrainingNum--;
		} elseif($result->getResult() == Question::RESULT_CORRECT) {
			$this->passedTrainingNum--;
		}

		$conn->update(TrainingTables::TRAINING_PROGRESS_TBL, [
			'passedTrainingNum' => $this->passedTrainingNum,
			'failedTrainingNum' => $this->failedTrainingNum
		], ['areaId' => $this->area->getId()]);
	}
	
	/**
	 * For confirming and counting trainngs without a test.
	 * 
	 * @throws Exception
	 */
	public function updateGoodFaithCompletion(Connection $conn)
	{
		$this->passedTrainingNum++;
		$conn->update(TrainingTables::TRAINING_PROGRESS_TBL, [
			'passedTrainingNum' => $this->passedTrainingNum,
			'failedTrainingNum' => $this->failedTrainingNum
		], ['areaId' => $this->area->getId()]);
		$this->refresh($conn);
	}
	
	public function refresh(Connection $conn)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.TrainingTables::TRAINING_PROGRESS_TBL.'` WHERE `areaId` = :id', array(':id' => $this->area->getId()));
		$this->mandatoryTrainingNum = $data['mandatoryTrainingNum'];
		$this->passedTrainingNum = $data['passedTrainingNum'];
		$this->failedTrainingNum = $data['failedTrainingNum'];
	}
}
