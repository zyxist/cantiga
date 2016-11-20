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
namespace WIO\EdkBundle\Entity;

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Doctrine\DBAL\Connection;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use WIO\EdkBundle\EdkTables;

/**
 * Single participant registration for Ex/W/C.
 *
 * @author Tomasz Jędrzejewski
 */
class EdkParticipant implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	private $id;
	/**
	 * @var EdkRegistrationSettings
	 */
	private $registrationSettings;
	private $accessKey;
	private $firstName;
	private $lastName;
	private $sex;
	private $age;
	private $email;
	private $peopleNum;
	private $customAnswer;
	private $howManyTimes;
	private $whyParticipate;
	private $whereLearnt;
	private $whereLearntOther;
	private $terms1Accepted;
	private $terms2Accepted;
	private $terms3Accepted;
	private $createdAt;
	private $ipAddress;
	
	/**
	 * Creates a new participant that is registered via the area leaders.
	 */
	public static function newParticipant()
	{
		$item = new EdkParticipant();
		$item->peopleNum = 1;
		return $item;
	}
	
	/**
	 * Fetches the participant by his/her access key.
	 * 
	 * @param Connection $conn
	 * @param string $key
	 * @param int $expectedAreaStatus
	 * @param boolean $forUpdate Whether to lock the registration settings for writing
	 * @return EdkParticipant
	 */
	public static function fetchByKey(Connection $conn, $key, $expectedAreaStatus, $forUpdate = true)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.EdkTables::PARTICIPANT_TBL.'` WHERE `accessKey` = :key', [':key' => $key]);
		if (false === $data) {
			return false;
		}
		$registrationSettings = EdkRegistrationSettings::fetchPublic($conn, $data['routeId'], $expectedAreaStatus, $forUpdate);
		
		if (empty($registrationSettings)) {
			return false;
		}
		
		$item = EdkParticipant::fromArray($data);
		$item->setRegistrationSettings($registrationSettings);
		return $item;
	}
	
	/**
	 * Fetches the participant by his/her ID and area.
	 * 
	 * @param Connection $conn
	 * @param int $id
	 * @param Area $area
	 * @param boolean $forUpdate Whether to lock the registration settings for writing
	 * @return EdkParticipant
	 */
	public static function fetchByArea(Connection $conn, $id, Area $area)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.EdkTables::PARTICIPANT_TBL.'` WHERE `id` = :id AND `areaId` = :areaId', [':id' => $id, ':areaId' => $area->getId()]);
		if (false === $data) {
			return false;
		}
		$route = EdkRoute::fetchByRoot($conn, $data['routeId'], $area);
		if (empty($route)) {
			return false;
		}
		$registrationSettings = EdkRegistrationSettings::fetchByRoute($conn, $route);
		
		if (empty($registrationSettings)) {
			return false;
		}
		$item = EdkParticipant::fromArray($data);
		$item->setRegistrationSettings($registrationSettings);
		return $item;
	}
	
	public static function fromArray($array, $prefix = '')
	{
		$item = new EdkParticipant;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return EdkRegistrationSettings
	 */
	public function getRegistrationSettings()
	{
		return $this->registrationSettings;
	}

	public function getAccessKey()
	{
		return $this->accessKey;
	}
	
	public function getName()
	{
		return $this->firstName.' '.$this->lastName;
	}

	public function getFirstName()
	{
		return $this->firstName;
	}

	public function getLastName()
	{
		return $this->lastName;
	}

	public function getSex()
	{
		return $this->sex;
	}

	public function getAge()
	{
		return $this->age;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getPeopleNum()
	{
		return $this->peopleNum;
	}

	public function getCustomAnswer()
	{
		return $this->customAnswer;
	}

	public function getHowManyTimes()
	{
		return $this->howManyTimes;
	}

	public function getWhyParticipate()
	{
		return $this->whyParticipate;
	}

	public function getWhereLearnt()
	{
		return $this->whereLearnt;
	}
	
	/**
	 * @return WhereLearntAbout
	 */
	public function getWhereLearntEntity()
	{
		return WhereLearntAbout::getItem($this->whereLearnt);
	}

	public function getWhereLearntOther()
	{
		return $this->whereLearntOther;
	}

	public function getTerms1Accepted()
	{
		return $this->terms1Accepted;
	}
	
	public function getTerms2Accepted()
	{
		return $this->terms2Accepted;
	}
	
	public function getTerms3Accepted()
	{
		return $this->terms3Accepted;
	}

	public function getCreatedAt()
	{
		return $this->createdAt;
	}
	
	public function getIpAddress()
	{
		return $this->ipAddress;
	}

	public function getSexText()
	{
		return ($this->sex == 1 ? 'SexMale' : 'SexFemale');
	}
	
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	public function setRegistrationSettings($registrationSettings)
	{
		$this->registrationSettings = $registrationSettings;
		return $this;
	}

	public function setAccessKey($accessKey)
	{
		$this->accessKey = $accessKey;
		return $this;
	}

	public function setFirstName($firstName)
	{
		$this->firstName = $firstName;
		return $this;
	}

	public function setLastName($lastName)
	{
		$this->lastName = $lastName;
		return $this;
	}

	public function setSex($sex)
	{
		$this->sex = $sex;
		return $this;
	}

	public function setAge($age)
	{
		$this->age = $age;
		return $this;
	}

	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	public function setPeopleNum($peopleNum)
	{
		$this->peopleNum = $peopleNum;
		return $this;
	}

	public function setCustomAnswer($customAnswer)
	{
		$this->customAnswer = $customAnswer;
		return $this;
	}

	public function setHowManyTimes($howManyTimes)
	{
		$this->howManyTimes = $howManyTimes;
		return $this;
	}

	public function setWhyParticipate($whyParticipate)
	{
		$this->whyParticipate = $whyParticipate;
		return $this;
	}

	public function setWhereLearnt($whereLearnt)
	{
		$this->whereLearnt = $whereLearnt;
		return $this;
	}

	public function setWhereLearntOther($whereLearntOther)
	{
		$this->whereLearntOther = $whereLearntOther;
		return $this;
	}

	public function setTerms1Accepted($terms1Accepted)
	{
		$this->terms1Accepted = $terms1Accepted;
		return $this;
	}
	
	public function setTerms2Accepted($terms2Accepted)
	{
		$this->terms2Accepted = $terms2Accepted;
		return $this;
	}
	
	public function setTerms3Accepted($terms3Accepted)
	{
		$this->terms3Accepted = $terms3Accepted;
		return $this;
	}

	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;
		return $this;
	}
	
	public function setIpAddress($addr)
	{
		$this->ipAddress = $addr;
		return $this;
	}
	
	/**
	 * Validates the constraints for the participant registration.
	 * 
	 * @param ExecutionContextInterface $context
	 */
	public function validate(ExecutionContextInterface $context) {
		$ok = true;
		if (!$this->terms1Accepted) {
			$context->buildViolation('TermsNotAcceptedErrorMsg')
				->atPath('terms1Accepted')
				->addViolation();
			$ok = false;
		}
		if (!$this->terms2Accepted) {
			$context->buildViolation('TermsNotAcceptedErrorMsg')
				->atPath('terms2Accepted')
				->addViolation();
			$ok = false;
		}
		if ($this->howManyTimes < 0) {
			$context->buildViolation('HowManyTimesWrongNumberErrorMsg')
				->atPath('howManyTimes')
				->addViolation();
			$ok = false;
		}
		
		if ($this->age < 1 || $this->age > 120) {
			$context->buildViolation('InvalidAgeErrorMsg')
				->atPath('age')
				->addViolation();
			$ok = false;
		}
		if (!empty($this->whereLearnt) && WhereLearntAbout::getItem($this->whereLearnt)->isCustom()) {
			if('' == trim($this->whereLearntOther)) {
				$context->buildViolation('WhereLearntOtherErrorMsg')
					->atPath('whereLearntOther')
					->addViolation();
				$ok = false;
			}
		}
		if ($this->getRegistrationSettings()->hasCustomQuestion()) {
			if('' == trim($this->customAnswer)) {
				$context->buildViolation('PleaseFillCustomAnswerErrorMsg')
					->atPath('customAnswer')
					->addViolation();
				$ok = false;
			}
		}
		
		$mpps = $this->getRegistrationSettings()->getMaxPeoplePerRecord();
		if($mpps != 1) {
			if($this->peopleNum > $mpps || $this->peopleNum < 1) {
				$context->buildViolation('RegisteredPeopleNumInvalidErrorMsg')
					->setParameter('%max%', $mpps)
					->atPath('peopleNum')
					->addViolation();
				$ok = false;
			}
		}
		if(!$this->getRegistrationSettings()->getAllowLimitExceed()) {
			if($this->peopleNum + $this->getRegistrationSettings()->getParticipantNum() > $this->getRegistrationSettings()->getParticipantLimit()) {
				$context->buildViolation('NoMorePlacesErrorMsg')
					->addViolation();
				$ok = false;
			}
		}
		return $ok;
	}

	public static function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addConstraint(new Callback('validate'));

		$metadata->addPropertyConstraint('firstName', new NotBlank());
		$metadata->addPropertyConstraint('firstName', new Length(array('min' => 2, 'max' => 50)));
		$metadata->addPropertyConstraint('lastName', new NotBlank());
		$metadata->addPropertyConstraint('lastName', new Length(array('min' => 2, 'max' => 50)));
		$metadata->addPropertyConstraint('whereLearnt', new Choice(array('choices' => WhereLearntAbout::getChoiceIds())));
		$metadata->addPropertyConstraint('age', new NotBlank());
		$metadata->addPropertyConstraint('email', new Email());
		$metadata->addPropertyConstraint('whyParticipate', new Length(array('min' => 2, 'max' => 200)));
		$metadata->addPropertyConstraint('howManyTimes', new NotBlank());
		$metadata->addPropertyConstraint('howManyTimes', new Range(['min' => 0]));
	}
	
	public function insert(Connection $conn)
	{
		$this->createdAt = time();
		$this->accessKey = sha1(uniqid(\microtime().$this->lastName.$this->whyParticipate, true));
		$conn->insert(
			EdkTables::PARTICIPANT_TBL,
			DataMappers::pick($this, ['accessKey', 'firstName', 'lastName', 'sex', 'age', 'email', 'peopleNum', 'customAnswer', 'whyParticipate', 'howManyTimes', 'whereLearnt', 'whereLearntOther', 'terms1Accepted', 'terms2Accepted', 'terms3Accepted', 'createdAt', 'ipAddress'], [
				'routeId' => $this->registrationSettings->getRoute()->getId(),
				'areaId' => $this->registrationSettings->getRoute()->getArea()->getId()
			])
		);
		$this->id = $conn->lastInsertId();
		$this->registrationSettings->registerParticipant($conn, $this);
		return $this->id;
	}

	public function update(Connection $conn)
	{
		$conn->update(
			EdkTables::PARTICIPANT_TBL,
			DataMappers::pick($this, ['firstName', 'lastName', 'sex', 'age', 'email', 'peopleNum', 'customAnswer', 'whyParticipate', 'howManyTimes', 'whereLearnt', 'whereLearntOther']),
			['id' => $this->id]
		);
	}
	
	public function canRemove()
	{
		return true;
	}

	public function remove(Connection $conn)
	{
		$conn->delete(EdkTables::PARTICIPANT_TBL, ['id' => $this->id]);
		$this->registrationSettings->unregisterParticipant($conn, $this);
	}
}
