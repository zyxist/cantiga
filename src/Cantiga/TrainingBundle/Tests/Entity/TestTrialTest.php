<?php
namespace Cantiga\TrainingBundle\Tests\Entity;

use Cantiga\TrainingBundle\Entity\Answer;
use Cantiga\TrainingBundle\Entity\Question;
use Cantiga\TrainingBundle\Entity\TestTrial;

class TestTrialTest extends \PHPUnit_Framework_TestCase {
	
	public function testValidatingFullPass() {
		// Given
		$trial = $this->createTestTrial();
		$data['question_0'] = $trial->getQuestions()[0]->getCorrectAnswerSet();
		$data['question_1'] = $trial->getQuestions()[1]->getCorrectAnswerSet();
		$data['question_2'] = $trial->getQuestions()[2]->getCorrectAnswerSet();
		$data['question_3'] = $trial->getQuestions()[3]->getCorrectAnswerSet();
		$data['question_4'] = $trial->getQuestions()[4]->getCorrectAnswerSet();
		
		// When
		$result = $trial->validateTestTrial($data);
		
		// Then
		$this->assertTrue($result);
		$this->assertEquals(Question::RESULT_CORRECT, $trial->getResult());
		$this->assertEquals(5, $trial->countPassedQuestions());
	}
	
	public function testValidatingPartialCorrectButSuccess() {
		// Given
		$trial = $this->createTestTrial();
		$data['question_0'] = $trial->getQuestions()[0]->getCorrectAnswerSet();
		$data['question_1'] = $trial->getQuestions()[1]->getCorrectAnswerSet();
		$data['question_2'] = $trial->getQuestions()[2]->getCorrectAnswerSet();
		$data['question_3'] = $trial->getQuestions()[3]->getCorrectAnswerSet();
		$data['question_4'] = $trial->getQuestions()[4]->getIncorrectAnswerSet();
		
		// When
		$result = $trial->validateTestTrial($data);
		
		// Then
		$this->assertTrue($result);
		$this->assertEquals(Question::RESULT_CORRECT, $trial->getResult());
		$this->assertEquals(4, $trial->countPassedQuestions());
	}
	
	public function testValidatingPartialCorrectButFailure() {
		// Given
		$trial = $this->createTestTrial();
		$data['question_0'] = $trial->getQuestions()[0]->getCorrectAnswerSet();
		$data['question_1'] = $trial->getQuestions()[1]->getCorrectAnswerSet();
		$data['question_2'] = $trial->getQuestions()[2]->getCorrectAnswerSet();
		$data['question_3'] = $trial->getQuestions()[3]->getIncorrectAnswerSet();
		$data['question_4'] = $trial->getQuestions()[4]->getIncorrectAnswerSet();
		
		// When
		$result = $trial->validateTestTrial($data);
		
		// Then
		$this->assertFalse($result);
		$this->assertEquals(Question::RESULT_INVALID, $trial->getResult());
		$this->assertEquals(3, $trial->countPassedQuestions());
	}
	
	private function createTestTrial() {
		return new TestTrial(5, array(
			new Question('Foo', new Answer('A', true), new Answer('B', true), new Answer('C', false), new Answer('D', false)),
			new Question('Bar', new Answer('A', true), new Answer('B', true), new Answer('C', true), new Answer('D', false)),
			new Question('Joe', new Answer('A', true), new Answer('B', false), new Answer('C', false), new Answer('D', false)),
			new Question('Moo', new Answer('A', false), new Answer('B', true), new Answer('C', false), new Answer('D', false)),
			new Question('Goo', new Answer('A', false), new Answer('B', false), new Answer('C', false), new Answer('D', true)),
		));
	}
}
