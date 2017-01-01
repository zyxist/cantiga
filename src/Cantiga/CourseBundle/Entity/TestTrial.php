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

use Cantiga\Metamodel\Exception\ModelException;
use RuntimeException;
use Serializable;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Represents a single trial to complete a test. It contains all the displayed questions in the randomized
 * order, and with randomized answers. The class is serializable and does not contain any references to the
 * outside world, so that it can be stored in a session to verify it in a later time.
 */
class TestTrial implements Serializable
{
	const PASSING_LEVEL = 0.8;
	
	private $trialQuestionNum;
	private $questions;
	private $result;
	
	public function __construct($trialQuestionNum = 0, array $allQuestions = array())
	{
		$this->trialQuestionNum = (int) $trialQuestionNum;
		$this->questions = $this->selectRandomQuestions($allQuestions);
		$this->result = Question::RESULT_UNKNOWN;
	}
	
	/**
	 * Generates a form with test questions.
	 * 
	 * @param FormBuilderInterface $fbi Controller-provided form builder
	 * @param TranslatorInterface $translator Translator for static labels
	 * @return The form
	 */
	public function generateTestForm(FormBuilderInterface $fbi, TranslatorInterface $translator)
	{
		foreach($this->questions as $idx => $question) {
			$question->generateFormField($fbi, $idx);
		}
		$fbi->add('save', SubmitType::class, array('label' => $translator->trans('Ready', [], 'course')));
		return $fbi->getForm();
	}
	
	/**
	 * Produces an array with the names of the fields with questions, so that
	 * the template could know what to render.
	 * 
	 * @return array
	 */
	public function generateFormFieldNames()
	{
		$results = [];
		foreach($this->questions as $idx => $question) {
			$results[] = $question->generateFieldName($idx);
		}
		return $results;
	}
	
	/**
	 * Verifies the solution sent by the user. The result is returned as a boolean, and stored
	 * in the <tt>result</tt> property.
	 * 
	 * @param array $results Data from the form
	 * @return boolean Whether the test has been correctly solved.
	 * @throws ModelException
	 */
	public function validateTestTrial(array $results)
	{
		$passed = 0;
		foreach($results as $resultId => $resultValues) {
			$resultId = (int) substr($resultId, 9);
			if(isset($this->questions[$resultId])) {
				$this->questions[$resultId]->validateAnswer($resultValues);
			} else {
				throw new ModelException('Unknown question ID: '.$resultId);
			}
			if($this->questions[$resultId]->getResult() == Question::RESULT_CORRECT) {
				$passed++;
			}
		}
		$score = $passed / (float)$this->trialQuestionNum;
		if($score >= self::PASSING_LEVEL) {
			$this->result = Question::RESULT_CORRECT;
		} else {
			$this->result = Question::RESULT_INVALID;
		}
		return ($this->result == Question::RESULT_CORRECT);
	}
	
	/**
	 * Returns an array with all the questions.
	 * 
	 * @return array
	 */
	public function getQuestions() {
		return $this->questions;
	}
	
	/**
	 * Returns the time lime limit (in minutes) required to solve the test.
	 * 
	 * @return int
	 */
	public function getTimeLimitInMinutes() {
		return $this->trialQuestionNum;
	}
	
	/**
	 * Returns the test results. The allowed flags are:
	 * 
	 * <ul>
	 *  <li><tt>Question::RESULT_UNKNOWN</tt> - not solved yet</li>
	 *  <li><tt>Question::RESULT_CORRECT</tt> - test solved correctly</li>
	 *  <li><tt>Question::RESULT_INVALID</tt> - test solved incorrectly</li>
	 * </ul>
	 * 
	 * @return int
	 */
	public function getResult()
	{
		return $this->result;
	}
	
	/**
	 * Returns the number of questions in the test.
	 * 
	 * @return int
	 */
	public function getQuestionNumber()
	{
		return sizeof($this->questions);
	}
	
	/**
	 * Returns the number of questions with the correct answer.
	 * 
	 * @return int
	 */
	public function countPassedQuestions() {
		$passed = 0;
		foreach($this->questions as $question) {
			if($question->getResult() == Question::RESULT_CORRECT) {
				$passed++;
			}
		}
		return $passed;
	}
	
	/**
	 * Returns the randomly specified group of questions from the poll of the available
	 * ones.
	 * 
	 * @param array $allQuestions
	 * @return array
	 * @throws RuntimeException
	 */
	private function selectRandomQuestions(array $allQuestions)
	{
		$selectedQuestions = array();
		$maxSize = sizeof($allQuestions);
		for($i = 0; $i < $this->trialQuestionNum; $i++) {
			$initialIndex = $selectedIndex = rand(0, $maxSize);
			
			while(!isset($allQuestions[$selectedIndex])) {
				$selectedIndex = ($selectedIndex + 1) % $maxSize;
				if($initialIndex == $selectedIndex) {
					throw new RuntimeException('I\'ve fallen into a cycle during the test trial generation! The test is not valid!');
				}
			}
			
			$selectedQuestions[] = $allQuestions[$selectedIndex];
			unset($allQuestions[$selectedIndex]);
		}
		return $selectedQuestions;
	}

	public function serialize()
	{
		return serialize(array(
			'trialQuestionNum' => $this->trialQuestionNum,
			'questions' => $this->questions
		));
	}

	public function unserialize($serialized)
	{
		$out = unserialize($serialized);
		$this->trialQuestionNum = $out['trialQuestionNum'];
		$this->questions = $out['questions'];
	}

}
