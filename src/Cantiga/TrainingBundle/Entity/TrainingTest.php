<?php
namespace Cantiga\TrainingBundle\Entity;

use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\TrainingBundle\Entity\Training;
use Cantiga\TrainingBundle\TrainingTables;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Represents a set of test questions to a training, specified in an XML format. The entity allows generating an
 * object model of a test, which can be used to render the final form.
 *
 * @author Tomasz Jędrzejewski
 */
class TrainingTest
{
	const QUESTION_NUM_ATTR = 'question-num';
	const CONTENT_ATTR = 'content';
	const CORRECT_ATTR = 'correct';
	const YES_VAL = 'yes';
	
	private $training;
	private $content;
	
	public function __construct(Training $training, $content)
	{
		$this->training = $training;
		$this->content = (string) $content;
	}
	
	public function setContent($content)
	{
		$this->content = (string) $content;
	}
	
	public function getContent()
	{
		return $this->content;
	}
	
	/**
	 * Constructs the test using the XML, and validates it.
	 */
	public function constructTestTrial($minQuestionNum)
	{
		$doc = simplexml_load_string($this->content);
		if(false === $doc) {
			throw new ModelException('This is not a valid XML file!');
		}
		if(!isset($doc[self::QUESTION_NUM_ATTR]) || $doc[self::QUESTION_NUM_ATTR] < $minQuestionNum) {
			throw new ModelException('Please specify the question number that is greater than or equal '.$minQuestionNum.'.');
		}
		$questionNum = (int) $doc[self::QUESTION_NUM_ATTR];
		if(!isset($doc->question) || sizeof($doc->question) < $questionNum) {
			throw new ModelException('Please define a sufficient number of questions!');
		}
		$questions = array();
		foreach($doc->question as $question) {
			if(sizeof($question->answer) != 4) {
				throw new ModelException('Each question shall have four answers.');
			}
			$questions[] = new Question($question[self::CONTENT_ATTR], 
				$this->xmlAnswerToAnswerObj($question->answer[0]),
				$this->xmlAnswerToAnswerObj($question->answer[1]),
				$this->xmlAnswerToAnswerObj($question->answer[2]),
				$this->xmlAnswerToAnswerObj($question->answer[3])
			);
		}
		return new TestTrial($questionNum, $questions);
	}
	
	private function xmlAnswerToAnswerObj($xmlAnswer)
	{
		return new Answer($xmlAnswer->__toString(), (isset($xmlAnswer[self::CORRECT_ATTR]) && $xmlAnswer[self::CORRECT_ATTR] == self::YES_VAL));
	}
	
	/**
	 * Saves the test question file in the database.
	 */
	public function save(Connection $conn)
	{
		$conn->executeUpdate('INSERT INTO `'.TrainingTables::TRAINING_TEST_TBL.'` (`trainingId`, `testStructure`) '
			. 'VALUES(:trainingId, :content1)'
			. 'ON DUPLICATE KEY UPDATE `testStructure` = :content2', array(':trainingId' => $this->training->getId(), ':content1' => $this->content, ':content2' => $this->content)
		);
		$conn->executeUpdate('UPDATE `'.TrainingTables::TRAINING_TBL.'` SET `lastUpdated` = :time WHERE `id` = :id', array(':id' => $this->training->getId(), ':time' => time()));
	}
	
	/**
	 * Downloads the XML file with test questions.
	 * 
	 * @param Response $response Response, where the file content should be printed out.
	 */
	public function download(Response $response)
	{
		$response->headers->set('Content-type', 'application/xml');
		$response->headers->set('Content-Disposition', 'attachment; filename="training_questions_'.$this->training->getId().'.xml"');
		$response->headers->set('Content-Length', strlen($this->content));
		$response->setContent($this->content);
	}
}
