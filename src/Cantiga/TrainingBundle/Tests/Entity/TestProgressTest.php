<?php
namespace Cantiga\TrainingBundle\Tests\Entity;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\AreaStatus;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\Territory;
use Cantiga\CoreBundle\Tests\Utils\DatabaseTestCase;
use Cantiga\TrainingBundle\Entity\Question;
use Cantiga\TrainingBundle\Entity\TestResult;
use Cantiga\TrainingBundle\Entity\TestTrial;
use Cantiga\TrainingBundle\Entity\TrainingProgress;
use Cantiga\TrainingBundle\TrainingTables;

class TestProgressTest extends DatabaseTestCase
{
	private $area;
	
	protected static function customSetup()
	{
		self::importFixture('training-test.sql');
	}
	
	public function setUp()
	{
		$this->project = Project::fetch(self::$conn, 1);
		$this->status = AreaStatus::fetchByProject(self::$conn, 1, $this->project);
		$this->territory = Territory::fetchByProject(self::$conn, 1, $this->project);
		
		$this->area = Area::newArea($this->project, $this->territory, $this->status, 'Area1');
		$this->area->insert(self::$conn);
		
		$pp = new TrainingProgress($this->area);
		$pp->insert(self::$conn);
	}
	
	public function tearDown()
	{
		self::$conn->executeUpdate('DELETE FROM `'.TrainingTables::TRAINING_RESULT_TBL.'`');
		self::$conn->executeUpdate('DELETE FROM `'.CoreTables::USER_TBL.'`');
		self::$conn->executeUpdate('DELETE FROM `'.TrainingTables::TRAINING_TBL.'`');
	}
	
	public function testUpdatingResultsFromIncompleteToPassed()
	{
		// Given
		$record = TrainingProgress::fetchByArea(self::$conn, $this->area);
		$result = $this->getTestResult(Question::RESULT_UNKNOWN);
		$trial = $this->getTestTrial(Question::RESULT_CORRECT);
		$this->fillRecord($this->area, 7, 3, 3);
		
		// When
		$record->updateResults(self::$conn, $result, $trial);
		
		// Then
		$this->expectTrainingProgress($this->area, 7, 4, 3);
		
	}
	
	public function testUpdatingResultsFromIncompleteToFailed()
	{
		// Given
		$record = TrainingProgress::fetchByArea(self::$conn, $this->area);
		$result = $this->getTestResult(Question::RESULT_UNKNOWN);
		$trial = $this->getTestTrial(Question::RESULT_INVALID);
		$this->fillRecord($this->area, 7, 3, 3);
		
		// When
		$record->updateResults(self::$conn, $result, $trial);
		
		// Then
		$this->expectTrainingProgress($this->area, 7, 3, 4);
	}
	
	public function testUpdatingResultsFromFailedtoPassed()
	{
		// Given
		$record = TrainingProgress::fetchByArea(self::$conn, $this->area);
		$result = $this->getTestResult(Question::RESULT_INVALID);
		$trial = $this->getTestTrial(Question::RESULT_INVALID);
		$this->fillRecord($this->area, 7, 3, 3);
		
		// When
		$record->updateResults(self::$conn, $result, $trial);
		
		// Then
		$this->expectTrainingProgress($this->area, 7, 3, 3);
	}
	
	public function testUpdatingResultsFromFailedtoFailed()
	{
		// Given
		$record = TrainingProgress::fetchByArea(self::$conn, $this->area);
		$result = $this->getTestResult(Question::RESULT_INVALID);
		$trial = $this->getTestTrial(Question::RESULT_CORRECT);
		$this->fillRecord($this->area, 7, 3, 3);
		
		// When
		$record->updateResults(self::$conn, $result, $trial);
		
		// Then
		$this->expectTrainingProgress($this->area, 7, 4, 2);
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
		self::$conn->update(TrainingTables::TRAINING_PROGRESS_TBL, array(
			'mandatoryTrainingNum' => $mandatory,
			'passedTrainingNum' =>$passed,
			'failedTrainingNum' => $failed
		), array('areaId' => $area->getId()));
	}
	
	private function expectTrainingProgress(Area $area, $mandatory, $passed, $failed)
	{
		$this->assertFieldEqualsEx(TrainingTables::TRAINING_PROGRESS_TBL, 'areaId', $area->getId(), 'mandatoryTrainingNum', $mandatory);
		$this->assertFieldEqualsEx(TrainingTables::TRAINING_PROGRESS_TBL, 'areaId', $area->getId(), 'passedTrainingNum', $passed);
		$this->assertFieldEqualsEx(TrainingTables::TRAINING_PROGRESS_TBL, 'areaId', $area->getId(), 'failedTrainingNum', $failed);
	}
}
