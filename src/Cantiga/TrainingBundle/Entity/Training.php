<?php
namespace Cantiga\TrainingBundle\Entity;

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\TrainingBundle\TrainingTables;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use LogicException;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Represents a single, on-line training, with a set of test questions.
 * Note that this class has been ported from the old code base, so it
 * does not do certain things in 'Cantiga' way.
 *
 * @author Tomasz Jędrzejewski
 */
class Training implements InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	private $id;
	private $name;
	private $description;
	private $authorName;
	private $authorEmail;
	private $lastUpdated;
	private $presentationLink;
	private $deadline;
	private $published = false;
	private $displayOrder = 1;
	private $notes = '';
	/**
	 *
	 * @var TrainingTest
	 */
	private $test;

	
	public static function fromArray($array, $prefix = '')
	{
		$item = new Training;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getName() 
	{
		return $this->name;
	}

	public function getAuthorName()
	{
		return $this->authorName;
	}

	public function getAuthorEmail() 
	{
		return $this->authorEmail;
	}

	public function getLastUpdated() 
	{
		return $this->lastUpdated;
	}

	public function getPresentationLink()
	{
		return $this->presentationLink;
	}

	public function getDeadline()
	{
		return $this->deadline;
	}

	public function getPublished()
	{
		return $this->published;
	}

	public function getDisplayOrder()
	{
		return $this->displayOrder;
	}

	public function getNotes()
	{
		return $this->notes;
	}

	public function setId($id)
	{
		DataMappers::noOverwritingId($this->id);
		$this->id = $id;
		return $this;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function setAuthorName($authorName)
	{
		$this->authorName = $authorName;
		return $this;
	}

	public function setAuthorEmail($authorEmail)
	{
		$this->authorEmail = $authorEmail;
		return $this;
	}

	public function setPresentationLink($presentationLink)
	{
		$this->presentationLink = $presentationLink;
		return $this;
	}

	public function setDeadline($deadline)
	{
		$this->deadline = $deadline;
		return $this;
	}

	public function setPublished($published)
	{
		$this->published = (bool) $published;
		return $this;
	}
	
	protected function setLastUpdated($lastUpdated)
	{
		$this->lastUpdated = $lastUpdated;
		return $this;
	}

	public function setDisplayOrder($displayOrder)
	{
		$this->displayOrder = (int) $displayOrder;
		return $this;
	}

	public function setNotes($notes)
	{
		$this->notes = $notes;
		return $this;
	}
	
	public function getDescription()
	{
		return $this->description;
	}

	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}
	
	/**
	 * Checks whether the deadline has not been reached.
	 * 
	 * @return boolean
	 */
	public function deadlineNotReached()
	{
		if($this->deadline === null) {
			return true;
		}
		return ($this->deadline->getTimestamp() > time());
	}
	
	/**
	 * Checks whether test questions have been generated for this training.
	 * 
	 * @return boolean
	 */
	public function hasTest()
	{
		return $this->test !== null;
	}
	
	/**
	 * Fetches an entity that represents a test question set. Note that it must be persisted
	 * separately.
	 * 
	 * @return TrainingTest
	 */
	public function getTest()
	{
		return $this->test;
	}

	/**
	 * Creates a new set of test questions for the test. It must be saved separately with
	 * <tt>getTest()->save()</tt> call.
	 * 
	 * @param string $content XML with the questions
	 */
	public function createTest($content)
	{
		$this->test = new TrainingTest($this, $content);
		return $this;
	}
	
	/**
	 * Certain trainings do not have a test. In this situation the user may click a button where he
	 * simply confirms in good-faith that he/she has completed the given training.
	 * 
	 * @param Connection $conn Database connection
	 * @param Area $area The area which finishes the training.
	 * @param User $user The user who completes the training.
	 */
	public function confirmGoodFaithCompletion(Connection $conn, Area $area, User $user)
	{
		if($this->hasTest()) {
			throw new ModelException('Cannot confirm good-faith completion for a training that has a test assigned.');
		}
		try {
			$stmt = $conn->prepare('INSERT INTO `'.TrainingTables::TRAINING_RESULT_TBL.'` '
				. '(`areaId`, `trainingId`, `trialNumber`, `startedAt`, `completedAt`, `result`, `totalQuestions`, `passedQuestions`) '
				. 'VALUES(:areaId, :trainingId,, :userId, 1, :startedAt, :completedAt, :result, :totalQuestions, :passedQuestions)');
			$stmt->bindValue(':areaId', $area->getId());
			$stmt->bindValue(':trainingId', $this->getId());
			$stmt->bindValue(':result', Question::RESULT_CORRECT);
			$stmt->bindValue(':startedAt', time());
			$stmt->bindValue(':completedAt', time());
			$stmt->bindValue(':totalQuestions', 1);
			$stmt->bindValue(':passedQuestions', 1);
			$stmt->execute();

			$area->getTrainingProgress()->updateGoodFaithCompletion();
		} catch(UniqueConstraintViolationException $exception) {
			throw new ModelException('Cannot complete a completed test!');
		}
	}

	public static function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('name', new NotBlank());
		$metadata->addPropertyConstraint('name', new Length(array('min' => 2, 'max' => 50)));
		$metadata->addPropertyConstraint('description', new Length(array('min' => 2, 'max' => 255)));
		$metadata->addPropertyConstraint('authorName', new NotBlank());
		$metadata->addPropertyConstraint('authorName', new Length(array('min' => 2, 'max' => 50)));
		$metadata->addPropertyConstraint('authorEmail', new NotBlank());
		$metadata->addPropertyConstraint('authorEmail', new Length(array('min' => 2, 'max' => 100)));
		$metadata->addPropertyConstraint('authorEmail', new Email());
		$metadata->addPropertyConstraint('presentationLink', new NotBlank());
		$metadata->addPropertyConstraint('presentationLink', new Url());
		$metadata->addPropertyConstraint('deadline', new Date());
		$metadata->addPropertyConstraint('displayOrder', new NotBlank());
		$metadata->addPropertyConstraint('displayOrder', new Range(array('min' => 0, 'max' => 100)));
		$metadata->addPropertyConstraint('notes', new Length(array('min' => 0, 'max' => 255)));
	}
	
	public function insert(Connection $conn)
	{
		if(null !== $this->getId()) {
			throw new LogicException('Cannot perform insert() on a persisted Training instance.');
		}

		$stmt = $conn->prepare('INSERT INTO `'.TrainingTables::TRAINING_TBL.'` (`name`, `description`, `authorName`, `authorEmail`, `lastUpdated`, `presentationLink`, `deadline`, `isPublished`, `displayOrder`, `notes`) '
			. 'VALUES(:name, :description, :authorName, :authorEmail, :lastUpdated, :presentationLink, :deadline, :isPublished, :displayOrder, :notes)');
		$stmt->bindValue(':name', $this->getName());
		$stmt->bindValue(':description', $this->getDescription());
		$stmt->bindValue(':authorName', $this->getAuthorName());
		$stmt->bindValue(':authorEmail', $this->getAuthorEmail());
		$stmt->bindValue(':lastUpdated', time());
		$stmt->bindValue(':presentationLink', $this->getPresentationLink());
		if(null !== $this->deadline) {
			$stmt->bindValue(':deadline', $this->deadline);
		} else {
			$stmt->bindValue(':deadline', null);
		}
		$stmt->bindValue(':isPublished', (int) $this->getPublished());
		$stmt->bindValue(':displayOrder', (int) $this->getDisplayOrder());
		$stmt->bindValue(':notes', $this->getNotes());
		$stmt->execute();

		$this->setId($conn->lastInsertId());

		if($this->getPublished()) {
			$this->incrementMandatoryTrainings($conn);
		}
		return $this->getId();
	}

	public function update(Connection $conn)
	{
		if(null === $this->getId()) {
			throw new LogicException('Cannot perform update() on an unpersisted Training instance.');
		}
		$oldPublished = (boolean) $conn->fetchColumn('SELECT `isPublished` FROM `'.TrainingTables::TRAINING_TBL.'` WHERE `id` = :id', array(':id' => $this->getId()));

		$stmt = $conn->prepare('UPDATE `'.TrainingTables::TRAINING_TBL.'` SET '
			. '`name` = :name,'
			. '`description` = :description,'
			. '`authorName` = :authorName,'
			. '`authorEmail` = :authorEmail,'
			. '`lastUpdated` = :lastUpdated,'
			. '`presentationLink` = :presentationLink,'
			. '`deadline` = :deadline,'
			. '`isPublished` = :isPublished,'
			. '`displayOrder` = :displayOrder,'
			. '`notes` = :notes WHERE `id` = :id');
		$stmt->bindValue(':id', $this->getId());
		$stmt->bindValue(':name', $this->getName());
		$stmt->bindValue(':description', $this->getDescription());
		$stmt->bindValue(':authorName', $this->getAuthorName());
		$stmt->bindValue(':authorEmail', $this->getAuthorEmail());
		$stmt->bindValue(':lastUpdated', time());
		$stmt->bindValue(':presentationLink', $this->getPresentationLink());
		if(null !== $this->deadline) {
			$stmt->bindValue(':deadline', $this->getDeadline());
		} else {
			$stmt->bindValue(':deadline', null);
		}
		$stmt->bindValue(':isPublished', (int) $this->getPublished());
		$stmt->bindValue(':displayOrder', (int) $this->getDisplayOrder());
		$stmt->bindValue(':notes', $this->getNotes());
		$stmt->execute();

		if($oldPublished !== $this->published) {
			if($this->published) {
				$this->incrementMandatoryTrainings($conn);
				$this->revokeTrainingFromAreaRecords($conn);
			} else {
				$this->decrementMandatoryTrainings($conn);
				$this->cancelTrainingFromAreaRecords($conn);
			}
		}
	}
	
	public function canRemove()
	{
		return true;
	}
	
	public function remove(Connection $conn)
	{
		if(null === $this->getId()) {
			throw new LogicException('Cannot perform remove() on an unpersisted Training instance.');
		}		
		$this->refresh($conn);
		if($this->published) {
			$this->decrementMandatoryTrainings($conn);
			$this->cancelTrainingFromAreaRecords($conn);
		}

		$conn->executeUpdate('DELETE FROM `'.TrainingTables::TRAINING_TBL.'` WHERE `id` = :id', array(':id' => $this->getId()));
	}
	
	/**
	 * TODO: move out of the entity to a separate renderer.
	 * 
	 * @return string
	 */
	public function generatePresentationCode()
	{
		if(preg_match('/docs.google.com\/presentation\/d\/([a-zA-Z0-9\-\_]+)\/(.*)/', $this->presentationLink, $matches)) {
			return '<iframe src="http://docs.google.com/presentation/embed?id='.$matches[1].'&amp;start=false&amp;loop=false&amp;" frameborder="0" width="800" height="520" allowfullscreen="true"></iframe>';
		} else if(strpos($this->presentationLink, 'http://prezi.com/embed/') !== false) {
			return '<iframe frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true" width="800" height="520" src="'.$this->presentationLink.'"></iframe>';
		} else {
			return '<p>Unknown presentation format.</p>';
		}
	}
	
	private function incrementMandatoryTrainings(Connection $conn)
	{
		$conn->query('UPDATE `'.TrainingTables::TRAINING_PROGRESS_TBL.'` SET `mandatoryTrainingNum` = (`mandatoryTrainingNum` + 1)');
	}
	
	private function decrementMandatoryTrainings(Connection $conn)
	{
		$conn->query('UPDATE `'.TrainingTables::TRAINING_PROGRESS_TBL.'` SET `mandatoryTrainingNum` = (`mandatoryTrainingNum` - 1)');
	}
	
	private function cancelTrainingFromAreaRecords(Connection $conn) {
		$conn->executeUpdate('UPDATE `'.TrainingTables::TRAINING_PROGRESS_TBL.'` r INNER JOIN `'.TrainingTables::TRAINING_RESULT_TBL.'` tt ON tt.`areaId` = r.`areaId` '
			. 'SET r.`passedTrainingNum` = (`passedTrainingNum` - 1)  WHERE tt.`result` = '.Question::RESULT_CORRECT.' AND tt.`trainingId` = :trainingId',
			array(':trainingId' => $this->getId()));
		$conn->executeUpdate('UPDATE `'.TrainingTables::TRAINING_PROGRESS_TBL.'` r INNER JOIN `'.TrainingTables::TRAINING_RESULT_TBL.'` tt ON tt.`areaId` = r.`areaId` '
			. 'SET r.`failedTrainingNum` = (`failedTrainingNum` - 1) WHERE tt.`result` = '.Question::RESULT_INVALID.' AND tt.`trainingId` = :trainingId',
			array(':trainingId' => $this->getId()));
	}
	
	private function revokeTrainingFromAreaRecords(Connection $conn)
	{
		$conn->executeUpdate('UPDATE `'.TrainingTables::TRAINING_PROGRESS_TBL.'` r INNER JOIN `'.TrainingTables::TRAINING_RESULT_TBL.'` tt ON tt.`areaId` = r.`areaId` '
			. 'SET r.`passedTrainingNum` = (`passedTrainingNum` + 1)  WHERE tt.`result` = '.Question::RESULT_CORRECT.' AND tt.`trainingId` = :trainingId',
			array(':trainingId' => $this->getId()));
		$conn->executeUpdate('UPDATE `'.TrainingTables::TRAINING_PROGRESS_TBL.'` r INNER JOIN `'.TrainingTables::TRAINING_RESULT_TBL.'` tt ON tt.`areaId` = r.`areaId` '
			. 'SET r.`failedTrainingNum` = (`failedTrainingNum` + 1) WHERE tt.`result` = '.Question::RESULT_INVALID.' AND tt.`trainingId` = :trainingId',
			array(':trainingId' => $this->getId()));
	}
	
	private function refresh(Connection $conn)
	{
		$this->published = (boolean) $conn->fetchColumn('SELECT `isPublished` FROM `'.TrainingTables::TRAINING_TBL.'` WHERE `id` = :id', array(':id' => $this->getId()));
	}
}
