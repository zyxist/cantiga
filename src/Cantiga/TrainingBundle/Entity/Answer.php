<?php
namespace Cantiga\TrainingBundle\Entity;

/**
 * Representation of a single possible answer to a training question.
 *
 * @author Tomasz Jędrzejewski
 */
class Answer
{
	private $answer;
	private $correct;
	
	public function __construct($answer, $correct)
	{
		$this->answer = (string) $answer;
		$this->correct = (boolean)$correct;
	}
	
	public function getAnswer()
	{
		return $this->answer;
	}

	public function isCorrect()
	{
		return $this->correct;
	}
}
