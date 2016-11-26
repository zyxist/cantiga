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
namespace Cantiga\CourseBundle\Tests\Entity;

use Cantiga\CourseBundle\Entity\Answer;
use Cantiga\CourseBundle\Entity\Question;
use Cantiga\CourseBundle\Entity\TestTrial;
use PHPUnit\Framework\TestCase;

class TestTrialTest extends TestCase {
	
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
