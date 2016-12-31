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

use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\DataMappers;
use Doctrine\DBAL\Connection;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use WIO\EdkBundle\EdkTables;

/**
 * Configuration of the participant registration for the EDK route.
 */
class EdkRegistrationSettings implements IdentifiableInterface, EditableEntityInterface
{
	const TYPE_NO = 0;
	const TYPE_EDK_WEBSITE = 1;
	const TYPE_OTHER_WEBSITE = 2;
	const TYPE_OTHER = 3;
	const TYPE_CLOSED = 4;
	
	private $route;
	private $registrationType;
	private $startTime;
	private $endTime;
	private $externalRegistrationUrl;
	private $participantLimit;
	private $allowLimitExceed;
	private $maxPeoplePerRecord;
	private $participantNum;
	private $externalParticipantNum;
	private $customQuestion;
	
	private $isNew = false;
	
	public static function fetchPublic(Connection $conn, $routeId, $expectedAreaStatus, $forUpdate = false)
	{
		$route = EdkRoute::fetchApproved($conn, $routeId);
		if (false === $route || $route->getArea()->getStatus()->getId() != $expectedAreaStatus) {
			return false;
		}
		
		$data = $conn->fetchAssoc('SELECT s.* FROM `'.EdkTables::REGISTRATION_SETTINGS_TBL.'` s WHERE `routeId` = :id AND `registrationType` = '.EdkRegistrationSettings::TYPE_EDK_WEBSITE.($forUpdate ? ' FOR UPDATE' : ''), [':id' => $routeId]);
		if (false === $data) {
			return false;
		}
		$item = self::fromArray($data);
		$item->route = $route;
		return $item;
	}
	
	public static function fetchByRoute(Connection $conn, EdkRoute $route)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.EdkTables::REGISTRATION_SETTINGS_TBL.'` WHERE `routeId` = :id', [':id' => $route->getId()]);
		if (false === $data) {
			$item = new EdkRegistrationSettings();
			$item->isNew = true;
		} else {
			$item = self::fromArray($data);
		}
		$item->route = $route;
		return $item;
	}
	
	public static function fromArray($array, $prefix = '')
	{
		$item = new EdkRegistrationSettings;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public static function getRelationships()
	{
		return ['route'];
	}
	
	public static function registrationTypeText($type)
	{
		switch ($type) {
			case self::TYPE_NO:
				return 'RegistrationTypeNo';
			case self::TYPE_EDK_WEBSITE:
				return 'RegistrationTypeEdkWebsite';
			case self::TYPE_OTHER_WEBSITE:
				return 'RegistrationTypeOtherWebsite';
			case self::TYPE_OTHER:
				return 'RegistrationTypeOther';
			case self::TYPE_CLOSED:
				return 'RegistrationTypeClosed';
		}
	}
	
	public static function getRegistrationTypes()
	{
		return [
			'RegistrationTypeNo' => self::TYPE_NO,
			'RegistrationTypeEdkWebsite' => self::TYPE_EDK_WEBSITE,
			'RegistrationTypeOtherWebsite' => self::TYPE_OTHER_WEBSITE,
			'RegistrationTypeOther' => self::TYPE_OTHER,
			'RegistrationTypeClosed' => self::TYPE_CLOSED
		];
	}
	
	/**
	 * Sprawdza niektore dodatkowe rzeczy
	 * 
	 * @param \WIO\AppBundle\Entity\ExecutionContextInterface $context
	 */
	public function validate(ExecutionContextInterface $context)
	{
		if ($this->startTime > $this->endTime) {
			$context->buildViolation('RegistrationTimeMismatchErrMsg')
				->atPath('endTime')
				->addViolation();
		}
		if ($this->registrationType == self::TYPE_NO) {
			$this->checkUnsupportedFields($context, ['externalRegistrationUrl', 'participantLimit', 'maxPeoplePerRecord', 'customQuestion']);
		} elseif ($this->registrationType == self::TYPE_EDK_WEBSITE) {
			$this->checkUnsupportedFields($context, ['externalRegistrationUrl']);
			if (empty($this->maxPeoplePerRecord)) {
				$context->buildViolation('MaxPeoplePerRecordRequired')
					->atPath('maxPeoplePerRecord')
					->addViolation();
			}
			if (empty($this->participantLimit)) {
				$context->buildViolation('FieldRequiredErrMsg')
					->atPath('participantLimit')
					->addViolation();
			}
		} elseif ($this->registrationType == self::TYPE_OTHER_WEBSITE) {
			if (empty($this->externalRegistrationUrl)) {
				$context->buildViolation('ExternalUrlMissingErrMsg')
					->atPath('externalRegistrationUrl')
					->addViolation();
			}
			$this->checkUnsupportedFields($context, ['participantLimit', 'maxPeoplePerRecord', 'customQuestion']);
		} elseif ($this->registrationType == self::TYPE_OTHER) {
			$this->checkUnsupportedFields($context, ['externalRegistrationUrl', 'participantLimit', 'maxPeoplePerRecord', 'customQuestion']);
		}
		if ($this->registrationType != self::TYPE_NO) {
			$this->externalParticipantNum = (int) $this->externalParticipantNum;
			if ($this->externalParticipantNum < 0) {
				$context->buildViolation('ExternalParticipantNumInvalidErrMsg')
					->atPath('externalParticipantNum')
					->addViolation();
			}
		}
	}
	
	private function checkUnsupportedFields(ExecutionContextInterface $context, array $fields)
	{
		foreach ($fields as $field) {
			if (!empty($this->$field)) {
				$context->buildViolation('FieldNotValidWithThisTypeErrMsg')
					->atPath($field)
					->addViolation();
			}
		}
	}

	public static function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addConstraint(new Callback('validate'));
		$metadata->addPropertyConstraint('registrationType', new NotBlank());
		$metadata->addPropertyConstraint('registrationType', new Range(array('min' => 0, 'max' => 4, 'invalidMessage' => 'InvalidRegistrationTypeErrMsg')));
		$metadata->addPropertyConstraint('startTime', new NotBlank());
		$metadata->addPropertyConstraint('endTime', new NotBlank());
		$metadata->addPropertyConstraint('participantLimit', new GreaterThanOrEqual(array('value' => 10, 'message' => 'MinimumParticipantLimitErrMsg')));
		$metadata->addPropertyConstraint('maxPeoplePerRecord', new Range(array('min' => 1, 'max' => 10, 'invalidMessage' => 'MaxAllowedPeopleErrMsg')));
		$metadata->addPropertyConstraint('customQuestion', new Length(array('min' => 0, 'max' => 250)));
	}
	
	public function getId()
	{
		return $this->route->getId();
	}

	/**
	 * @return EdkRoute
	 */
	public function getRoute()
	{
		return $this->route;
	}
	
	public function getName()
	{
		return $this->route->getName();
	}

	public function getRegistrationType()
	{
		return $this->registrationType;
	}
	
	public function getRegistrationTypeText()
	{
		return self::registrationTypeText($this->registrationType);
	}

	public function getStartTime()
	{
		return $this->startTime;
	}

	public function getEndTime()
	{
		return $this->endTime;
	}

	public function getExternalRegistrationUrl()
	{
		return $this->externalRegistrationUrl;
	}

	public function getParticipantLimit()
	{
		return $this->participantLimit;
	}

	public function getAllowLimitExceed()
	{
		return $this->allowLimitExceed;
	}

	public function getMaxPeoplePerRecord()
	{
		return $this->maxPeoplePerRecord;
	}

	public function getParticipantNum()
	{
		return $this->participantNum;
	}
	
	public function getExternalParticipantNum()
	{
		return $this->externalParticipantNum;
	}

	public function getCustomQuestion()
	{
		return $this->customQuestion;
	}

	public function setRoute(EdkRoute $route)
	{
		$this->route = $route;
		return $this;
	}

	public function setRegistrationType($registrationType)
	{
		$this->registrationType = $registrationType;
		return $this;
	}

	public function setStartTime($startTime)
	{
		$this->startTime = $startTime;
		return $this;
	}

	public function setEndTime($endTime)
	{
		$this->endTime = $endTime;
		return $this;
	}

	public function setExternalRegistrationUrl($externalRegistrationUrl)
	{
		$this->externalRegistrationUrl = $externalRegistrationUrl;
		return $this;
	}

	public function setParticipantLimit($participantLimit)
	{
		$this->participantLimit = $participantLimit;
		return $this;
	}

	public function setAllowLimitExceed($allowLimitExceed)
	{
		$this->allowLimitExceed = $allowLimitExceed;
		return $this;
	}

	public function setMaxPeoplePerRecord($maxPeoplePerRecord)
	{
		$this->maxPeoplePerRecord = $maxPeoplePerRecord;
		return $this;
	}

	public function setParticipantNum($participantNum)
	{
		$this->participantNum = $participantNum;
		return $this;
	}
	
	public function setExternalParticipantNum($participantNum)
	{
		$this->externalParticipantNum = $participantNum;
		return $this;
	}

	public function setCustomQuestion($customQuestion)
	{
		$this->customQuestion = $customQuestion;
		return $this;
	}
	
	public function hasCustomQuestion()
	{
		return !empty($this->customQuestion);
	}
	
	/**
	 * Returns <strong>true</strong>, if the registration is currently open.
	 * 
	 * @return boolean
	 */
	public function isRegistrationOpen() {
		$time = time();
		if ($this->registrationType == self::TYPE_NO || $this->registrationType == self::TYPE_CLOSED || !($time >= $this->startTime && $time <= $this->endTime)) {
			return false;
		}
		return $this->isRegistrationAllowed();
	}

	public function isRegistrationAllowed() {
		if ($this->allowLimitExceed) {
			return true;
		}
		return $this->participantNum < $this->participantLimit;
	}

	public function update(Connection $conn)
	{
		if ($this->isNew) {
			$conn->insert(
				EdkTables::REGISTRATION_SETTINGS_TBL,
				DataMappers::pick($this, ['route', 'registrationType', 'startTime', 'endTime', 'externalRegistrationUrl', 'participantLimit', 'allowLimitExceed', 'maxPeoplePerRecord', 'externalParticipantNum', 'customQuestion'], [
					'areaId' => $this->route->getArea()->getId()
				])
			);
		} else {
			$conn->update(
				EdkTables::REGISTRATION_SETTINGS_TBL,
				DataMappers::pick($this, ['registrationType', 'startTime', 'endTime', 'externalRegistrationUrl', 'participantLimit', 'allowLimitExceed', 'maxPeoplePerRecord', 'externalParticipantNum', 'customQuestion']),
				['routeId' => $this->route->getId()]
			);
		}
		$conn->update(EdkTables::ROUTE_TBL, ['updatedAt' => time()], ['id' => $this->route->getId()]);
		return $this->route->getId();
	}
	
	public function registerParticipant(Connection $conn, EdkParticipant $participant)
	{
		$count = $conn->fetchColumn('SELECT SUM(`peopleNum`) FROM `'.EdkTables::PARTICIPANT_TBL.'` WHERE `routeId` = :routeId', [':routeId' => $this->route->getId()]);
		$conn->update(EdkTables::REGISTRATION_SETTINGS_TBL, ['participantNum' => $count], ['routeId' => $this->route->getId()]);
	}
	
	public function unregisterParticipant(Connection $conn, EdkParticipant $participant)
	{
		$count = $conn->fetchColumn('SELECT SUM(`peopleNum`) FROM `'.EdkTables::PARTICIPANT_TBL.'` WHERE `routeId` = :routeId', [':routeId' => $this->route->getId()]);
		$conn->update(EdkTables::REGISTRATION_SETTINGS_TBL, ['participantNum' => $count], ['routeId' => $this->route->getId()]);
	}
}
