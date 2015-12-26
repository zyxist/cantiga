<?php
namespace Cantiga\TrainingBundle\Tests\Entity;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\AreaStatus;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\Territory;
use Cantiga\CoreBundle\Tests\Utils\DatabaseTestCase;
use Cantiga\TrainingBundle\Entity\Question;
use Cantiga\TrainingBundle\Entity\Training;
use Cantiga\TrainingBundle\Entity\TrainingProgress;
use Cantiga\TrainingBundle\TrainingTables;

class TrainingTest extends DatabaseTestCase {
	private $project;
	private $territory;
	private $status;
	
	private $area;
	private $area2;
	
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
		$this->area2 = Area::newArea($this->project, $this->territory, $this->status, 'Area2');
		
		$tpa1 = new TrainingProgress($this->area);
		$tpa1->insert(self::$conn);
	}
	
	public function tearDown()
	{
		self::$conn->executeUpdate('DELETE FROM `'.TrainingTables::TRAINING_RESULT_TBL.'`');
		self::$conn->executeUpdate('DELETE FROM `'.CoreTables::AREA_TBL.'`');
		self::$conn->executeUpdate('DELETE FROM `'.TrainingTables::TRAINING_TBL.'`');
	}
	
	public function testCreatingPublishedTraining()
	{
		// Given
		$training = new Training();
		$training->setName('Foo');
		$this->setDefaults($training);
		$training->setPublished(true);
		
		$this->fillRecord($this->area, 3, 2, 1);
		
		// When
		$training->insert(self::$conn);
		
		// Then
		$this->assertExists(TrainingTables::TRAINING_TBL, $training->getId());
		$this->expectTrainingProgress($this->area, 4, 2, 1);
	}
	
	public function testCreatingUnpublishedTraining()
	{
		// Given
		$training = new Training();
		$training->setName('Foo');
		$this->setDefaults($training);
		$training->setPublished(false);
		
		$this->fillRecord($this->area, 3, 2, 1);
		
		// When
		$training->insert(self::$conn);
		
		// Then
		$this->assertExists(TrainingTables::TRAINING_TBL, $training->getId());
		$this->expectTrainingProgress($this->area, 3, 2, 1);
	}
	
	public function testSwitchingFromPublishedToUnpublished()
	{
		// Given
		$this->area2->insert(self::$conn);
		$tpa2 = new TrainingProgress($this->area2);
		$tpa2->insert(self::$conn);
		
		$training = new Training();
		$training->setName('Foo');
		$this->setDefaults($training);
		$training->setPublished(true);
		$training->insert(self::$conn);
		
		$this->insertResult($training, $this->area, Question::RESULT_CORRECT);
		$this->insertResult($training, $this->area2, Question::RESULT_INVALID);
		
		$this->fillRecord($this->area, 3, 2, 1);
		$this->fillRecord($this->area2, 3, 2, 1);
		
		// When
		$training->setPublished(false);
		$training->update(self::$conn);
		
		// Then
		$this->assertExists(TrainingTables::TRAINING_TBL, $training->getId());
		$this->expectTrainingProgress($this->area, 2, 1, 1);
		$this->expectTrainingProgress($this->area2, 2, 2, 0);
	}
	
	public function testSwitchingFromUnpublishedToPublished()
	{
		// Given
		$this->area2->insert(self::$conn);
		$tpa2 = new TrainingProgress($this->area2);
		$tpa2->insert(self::$conn);
		
		$training = new Training();
		$training->setName('Foo');
		$this->setDefaults($training);
		$training->setPublished(false);
		$training->insert(self::$conn);
		
		$this->insertResult($training, $this->area, Question::RESULT_CORRECT);
		$this->insertResult($training, $this->area2, Question::RESULT_INVALID);
		
		$this->fillRecord($this->area, 3, 2, 1);
		$this->fillRecord($this->area2, 3, 2, 1);
		
		// When
		$training->setPublished(true);
		$training->update(self::$conn);
		
		// Then
		$this->assertExists(TrainingTables::TRAINING_TBL, $training->getId());
		$this->expectTrainingProgress($this->area, 4, 3, 1);
		$this->expectTrainingProgress($this->area2, 4, 2, 2);
	}
	
	public function testDeletingPublishedTraining()
	{
		// Given
		$this->area2->insert(self::$conn);
		$tpa2 = new TrainingProgress($this->area2);
		$tpa2->insert(self::$conn);
		
		$training = new Training();
		$training->setName('Foo');
		$this->setDefaults($training);
		$training->setPublished(true);
		$training->insert(self::$conn);
		
		$this->insertResult($training, $this->area, Question::RESULT_CORRECT);
		$this->insertResult($training, $this->area2, Question::RESULT_INVALID);
		
		$this->fillRecord($this->area, 3, 2, 1);
		$this->fillRecord($this->area2, 3, 2, 1);
		
		// When
		$training->remove(self::$conn);
		
		// Then
		$this->assertNotExists(TrainingTables::TRAINING_TBL, $training->getId());
		$this->expectTrainingProgress($this->area, 2, 1, 1);
		$this->expectTrainingProgress($this->area2, 2, 2, 0);
	}
	
	public function testDeletingUnpublishedTraining()
	{
		// Given
		$this->area2->insert(self::$conn);
		$tpa2 = new TrainingProgress($this->area2);
		$tpa2->insert(self::$conn);
		
		$training = new Training();
		$training->setName('Foo');
		$this->setDefaults($training);
		$training->setPublished(false);
		$training->insert(self::$conn);
		
		$this->insertResult($training, $this->area, Question::RESULT_CORRECT);
		$this->insertResult($training, $this->area2, Question::RESULT_INVALID);
		
		$this->fillRecord($this->area, 3, 2, 1);
		$this->fillRecord($this->area2, 3, 2, 1);
		
		// When
		$training->remove(self::$conn);
		
		// Then
		$this->assertNotExists(TrainingTables::TRAINING_TBL, $training->getId());
		$this->expectTrainingProgress($this->area, 3, 2, 1);
		$this->expectTrainingProgress($this->area2, 3, 2, 1);
	}
	
	private function setDefaults(Training $training)
	{
		$training->setAuthorName('Johnny B.');
		$training->setAuthorEmail('johnny-b@example.com');
		$training->setPresentationLink('http://www.example.com/');
		$training->setDisplayOrder(1);
		$training->setDescription('foo');
		$training->setNotes('foo');
	}
	
	private function insertResult(Training $training, Area $area, $result)
	{
		self::$conn->insert(TrainingTables::TRAINING_RESULT_TBL, array(
			'areaId' => $area->getId(),
			'trainingId' => $training->getId(),
			'trialNumber' => 1,
			'startedAt' => time(),
			'completedAt' => time(),
			'result' => $result,
			'totalQuestions' => 0,
			'passedQuestions' => 0
		));
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
