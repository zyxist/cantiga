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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Represents a single question with four answers.
 *
 * @author Tomasz Jędrzejewski
 */
class Question
{
	const RESULT_UNKNOWN = 0;
	const RESULT_CORRECT = 1;
	const RESULT_INVALID = 2;
	
	private $content;
	private $answers;
	private $result = self::RESULT_UNKNOWN;
	
	private $correctAnswerNum = 0;
	
	public function __construct($content, Answer $answer1, Answer $answer2, Answer $answer3, Answer $answer4)
	{
		$this->content = (string) $content;
		$this->answers = array($answer1, $answer2, $answer3, $answer4);
		shuffle($this->answers);
		
		foreach($this->answers as $answer) {
			if($answer->isCorrect()) {
				$this->correctAnswerNum++;
			}
		}
		if($this->correctAnswerNum == 0) {
			throw new ModelException('Cannot create a question with no correct answers!');
		}
	}
	
	/**
	 * Generates a single form field for the test form.
	 * 
	 * @param FormBuilderInterface $fbi
	 * @param int $idx Number of the question in this trial.
	 */
	public function generateFormField(FormBuilderInterface $fbi, $idx)
	{
		$fbi->add($this->generateFieldName($idx), new ChoiceType, array(
			'label' => $this->content,
			'expanded' => true,
			'multiple' => $this->correctAnswerNum != 1,
			'choices' => $this->answersToArray()
		));
	}
	
	public function generateFieldName($idx)
	{
		return 'question_'.$idx;
	}
	
	/**
	 * Verifies the user answer to the question.
	 * 
	 * @param array|int $results
	 * @throws ModelException
	 */
	public function validateAnswer($results)
	{
		if($this->correctAnswerNum == 1 && !is_array($results)) {
			$answerNumber = $this->sanitizeAnswerNum($results);
			if($this->answers[$answerNumber]->isCorrect()) {
				$this->result = self::RESULT_CORRECT;
			}
		} elseif($this->correctAnswerNum > 1 && is_array($results)) {
			if(sizeof($results) != $this->correctAnswerNum) {
				$this->result = self::RESULT_INVALID;
			} else {
				$ok = true;
				foreach($results as $answerNumber) {
					$answerNumber = $this->sanitizeAnswerNum($answerNumber);
					if(!$this->answers[$answerNumber]->isCorrect()) {
						$ok = false;
					}
				}
				$this->result = ($ok ? self::RESULT_CORRECT : self::RESULT_INVALID);
			}
		} else {
			throw new ModelException('Invalid result set for the answer: '.$this->content.' ');
		}
	}
	
	/**
	 * Used in the automated tests - returns the "form result" that represents the correct answer
	 * to the question.
	 * 
	 * @return array|int
	 */
	public function getCorrectAnswerSet()
	{
		if($this->correctAnswerNum == 1) {
			foreach($this->answers as $id => $answer) {
				if($answer->isCorrect()) {
					return $id;
				}
			}
			return 0;
		} else {
			$correct = array();
			foreach($this->answers as $id => $answer) {
				if($answer->isCorrect()) {
					$correct[] = $id;
				}
			}
			return $correct;
		}
	}
	
	/**
	 * Used in the automated tests - returns the "form result" that represents the invalid
	 * answer to the question.
	 * 
	 * @return array|int
	 */
	public function getIncorrectAnswerSet() {
		if($this->correctAnswerNum == 1) {
			foreach($this->answers as $id => $answer) {
				if(!$answer->isCorrect()) {
					return $id;
				}
			}
			return 0;
		} else {
			$correct = array();
			foreach($this->answers as $id => $answer) {
				if(!$answer->isCorrect()) {
					$correct[] = $id;
				}
			}
			return $correct;
		}
	}
	
	public function getResult()
	{
		return $this->result;
	}
	
	private function answersToArray()
	{
		$result = array();
		foreach($this->answers as $answer) {
			$result[] = $answer->getAnswer();
		}
		return $result;
	}
	
	private function sanitizeAnswerNum($answerNum)
	{
		if($answerNum < 0 || $answerNum > 3) {
			throw new ModelException('Invalid answer number: '.$answerNum);
		}
		return (int) $answerNum;
	}
}
