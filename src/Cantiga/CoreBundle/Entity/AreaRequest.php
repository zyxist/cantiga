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
namespace Cantiga\CoreBundle\Entity;

use Cantiga\Components\Hierarchy\MembershipRoleResolverInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\UserBundle\Entity\ContactData;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AreaRequest implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	const STATUS_NEW = 0;
	const STATUS_VERIFICATION = 1;
	const STATUS_APPROVED = 2;
	const STATUS_REVOKED = 3;

	private $id;
	private $name;
	private $project;
	private $requestor;
	private $verifier;
	private $territory;
	private $customData;
	private $createdAt;
	private $lastUpdatedAt;
	private $status = self::STATUS_NEW;
	private $commentNum;
	private $postedMessage = null;
	
	private $contactData;
	private $oldTerritory;
	
	public static function fetchByRequestor(Connection $conn, $id, User $requestor)
	{
		$data = $conn->fetchAssoc('SELECT r.*, v.id AS `verifier_id`, v.`name` AS `verifier_name`, '
			. 't.`id` AS `territory_id`, t.`name` AS `territory_name`, t.`areaNum` AS `territory_areaNum`, t.`requestNum` as `territory_requestNum` '
			. 'FROM `'.CoreTables::AREA_REQUEST_TBL.'` r '
			. 'INNER JOIN `'.CoreTables::TERRITORY_TBL.'` t ON t.`id` = r.`territoryId` '
			. 'LEFT JOIN `'.CoreTables::USER_TBL.'` v ON v.`id` = r.`verifierId` '
			. 'WHERE r.`id` = :id AND r.`requestorId` = :requestorId', [':id' => $id, ':requestorId' => $requestor->getId()]);
		if(null === $data) {
			return false;
		}
		$project = Project::fetch($conn, $data['projectId']);
		$item = self::fromArray($data);
		$item->setProject($project);
		$item->setRequestor($requestor);
		if (!empty($data['verifier_id'])) {
			$item->verifier = new Verifier($data['verifier_id'], $data['verifier_name']);
		}
		$item->setTerritory($item->oldTerritory = Territory::fromArray($data, 'territory'));
		return $item;
	}
	
	public static function fetchByProject(Connection $conn, $id, Project $project)
	{
		$data = $conn->fetchAssoc('SELECT r.*, v.id AS `verifier_id`, v.`name` AS `verifier_name`, '
			. 't.`id` AS `territory_id`, t.`name` AS `territory_name`, t.`areaNum` AS `territory_areaNum`, t.`requestNum` as `territory_requestNum` '
			. 'FROM `'.CoreTables::AREA_REQUEST_TBL.'` r '
			. 'INNER JOIN `'.CoreTables::TERRITORY_TBL.'` t ON t.`id` = r.`territoryId` '
			. 'LEFT JOIN `'.CoreTables::USER_TBL.'` v ON v.`id` = r.`verifierId` '
			. 'WHERE r.`id` = :id AND r.`projectId` = :projectId', [':id' => $id, ':projectId' => $project->getId()]);
		if(null === $data) {
			return false;
		}
		$user = User::fetchByCriteria($conn, QueryClause::clause('u.`id` = :id', ':id', $data['requestorId']));
		$item = self::fromArray($data);
		$item->setProject($project);
		$item->setRequestor($user);
		if (!empty($data['verifier_id'])) {
			$item->verifier = new Verifier($data['verifier_id'], $data['verifier_name']);
		}
		$item->setTerritory($item->oldTerritory = Territory::fromArray($data, 'territory'));
		return $item;
	}
	
	public static function fromArray($array, $prefix = '')
	{
		$item = new AreaRequest;
		if (isset($array['customData'])) {
			$item->customData = json_decode($array['customData'], true);
			unset($array['customData']);
		}
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}

	public static function getRelationships()
	{
		return ['project', 'requestor'];
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata) {
		$metadata->addPropertyConstraint('name', new NotBlank());
		$metadata->addPropertyConstraint('name', new Length(array('min' => 2, 'max' => 100)));
	}

	public function getId()
	{
		return $this->id;
	}
	
	public function setId($id)
	{
		DataMappers::noOverwritingId($this->id);
		$this->id = $id;
		return $this;
	}
	
	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	public function getProject()
	{
		return $this->project;
	}

	public function setProject($project)
	{
		$this->project = $project;
		return $this;
	}
	
	public function getCustomData()
	{
		return $this->customData;
	}

	public function setCustomData(array $customData)
	{
		$this->customData = $customData;
		return $this;
	}
	
	/**
	 * Fetches a value of a custom property. Null value is returned,
	 * if the property is not set or it is empty.
	 * 
	 * @param string $name custom property name
	 * @return mixed
	 */
	public function get($name)
	{
		if (!isset($this->customData[$name])) {
			return null;
		}
		return $this->customData[$name];
	}
	
	public function getRequestor()
	{
		return $this->requestor;
	}

	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	public function getLastUpdatedAt()
	{
		return $this->lastUpdatedAt;
	}
	
	/**
	 * @return Territory
	 */
	public function getTerritory()
	{
		return $this->territory;
	}
	
	public function setTerritory(Territory $territory)
	{
		$this->territory = $territory;
		return $this;
	}

	public function getStatus()
	{
		return $this->status;
	}
	
	/**
	 * @return Verifier|User
	 */
	public function getVerifier()
	{
		return $this->verifier;
	}
	
	public function getContactData()
	{
		return $this->contactData;
	}

	public function setContactData(ContactData $contactData): self
	{
		$this->contactData = $contactData;
		return $this;
	}
		
	public static function statusLabel($status)
	{
		switch($status) {
			case self::STATUS_NEW:
				return 'default';
			case self::STATUS_VERIFICATION:
				return 'primary';
			case self::STATUS_APPROVED:
				return 'success';
			case self::STATUS_REVOKED:
				return 'danger';
		}
	}
	
	public static function statusText($status)
	{
		switch($status) {
			case self::STATUS_NEW:
				return 'New request';
			case self::STATUS_VERIFICATION:
				return 'Verification';
			case self::STATUS_APPROVED:
				return 'Approved';
			case self::STATUS_REVOKED:
				return 'Declined';
		}
	}
	
	public static function statusList()
	{
		return [
			self::STATUS_NEW => self::statusText(self::STATUS_NEW),
			self::STATUS_VERIFICATION => self::statusText(self::STATUS_VERIFICATION),
			self::STATUS_APPROVED => self::statusText(self::STATUS_APPROVED),
			self::STATUS_REVOKED => self::statusText(self::STATUS_REVOKED),
		];
	}
	
	public function getStatusLabel()
	{
		return self::statusLabel($this->status);
	}
	
	public function getStatusText()
	{
		return self::statusText($this->status);
	}

	public function setRequestor($requestor)
	{
		$this->requestor = $requestor;
		return $this;
	}

	public function setCreatedAt($createdAt)
	{
		DataMappers::noOverwritingField($this->createdAt);
		$this->createdAt = $createdAt;
		return $this;
	}

	public function setLastUpdatedAt($lastUpdated)
	{
		DataMappers::noOverwritingField($this->lastUpdatedAt);
		$this->lastUpdatedAt = $lastUpdated;
		return $this;
	}

	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}
	
	public function getCommentNum()
	{
		return $this->commentNum;
	}

	public function setCommentNum($commentNum)
	{
		DataMappers::noOverwritingField($this->commentNum);
		$this->commentNum = $commentNum;
		return $this;
	}

	public function canRemove()
	{
		return (!$this->project->getArchived() && ($this->status == 0 || $this->status == 1));
	}
	
	public function post(Message $message)
	{
		$this->postedMessage = $message;
	}

	public function insert(Connection $conn)
	{
		$this->createdAt = $this->lastUpdatedAt = time();
		
		DataMappers::recount($conn, CoreTables::TERRITORY_TBL, null, $this->territory, 'requestNum', 'id');
		
		$conn->insert(
			CoreTables::AREA_REQUEST_TBL,
			DataMappers::pick($this, ['name', 'project', 'territory', 'requestor', 'createdAt', 'lastUpdatedAt', 'status'], ['customData' => json_encode($this->customData)])
		);
		return $this->id = $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		$this->lastUpdatedAt = time();
		if (null !== $this->postedMessage) {
			$this->commentNum = $conn->fetchColumn('SELECT `commentNum` FROM `'.CoreTables::AREA_REQUEST_TBL.'` WHERE `id` = :id', [':id' => $this->id]);
			$conn->insert(CoreTables::AREA_REQUEST_COMMENT_TBL, [
				'requestId' => $this->id,
				'userId' => $this->postedMessage->getUser()->getId(),
				'createdAt' => $this->postedMessage->getCreatedAt(),
				'message' => $this->postedMessage->getMessage()
			]);
			$this->commentNum++;
		}
		
		if (!DataMappers::same($this->oldTerritory, $this->territory)) {
			DataMappers::recount($conn, CoreTables::TERRITORY_TBL, $this->oldTerritory, $this->territory, 'requestNum', 'id');
		}
		
		$cnt = $conn->update(
			CoreTables::AREA_REQUEST_TBL,
			DataMappers::pick($this, ['name', 'status', 'territory', 'lastUpdatedAt', 'commentNum'], ['customData' => json_encode($this->customData)]),
			DataMappers::pick($this, ['id'])
		);
		return $cnt;
	}
	
	public function remove(Connection $conn)
	{
		$this->status = $conn->fetchColumn('SELECT `status` FROM `'.CoreTables::AREA_REQUEST_TBL.'` WHERE `id` = :id', [':id' => $this->id]);
		if ($this->canRemove()) {
			DataMappers::recount($conn, CoreTables::TERRITORY_TBL, $this->territory, null, 'requestNum', 'id');
			$conn->delete(CoreTables::AREA_REQUEST_TBL, DataMappers::pick($this, ['id']));
		}
	}
	
	public function startVerification(Connection $conn, User $verifier)
	{
		$this->status = $conn->fetchColumn('SELECT `status` FROM `'.CoreTables::AREA_REQUEST_TBL.'` WHERE `id` = :id', [':id' => $this->id]);
		if ($this->status == self::STATUS_NEW) {
			$this->lastUpdatedAt = time();
			$this->status = self::STATUS_VERIFICATION;
			$this->verifier = $verifier;

			$conn->update(CoreTables::AREA_REQUEST_TBL,
				['lastUpdatedAt' => $this->lastUpdatedAt, 'status' => $this->status, 'verifierId' => $this->verifier->getId()],
				['id' => $this->getId()]
			);
			return true;
		}
		return false;
	}
	
	public function approve(Connection $conn, MembershipRoleResolverInterface $resolver)
	{
		$this->status = $conn->fetchColumn('SELECT `status` FROM `'.CoreTables::AREA_REQUEST_TBL.'` WHERE `id` = :id', [':id' => $this->id]);
		if ($this->status == self::STATUS_VERIFICATION) {
			$this->lastUpdatedAt = time();
			$this->status = self::STATUS_APPROVED;

			$conn->update(CoreTables::AREA_REQUEST_TBL,
				['lastUpdatedAt' => $this->lastUpdatedAt, 'status' => $this->status],
				['id' => $this->getId()]
			);
			
			$area = new Area();
			$area->setName($this->name);
			$area->setProject($this->project);
			$area->setTerritory($this->territory);
			$area->setReporter($this->requestor); // oops, naming inconsistency
			$id = $area->insert($conn);
			$conn->update(CoreTables::AREA_REQUEST_TBL,
				['areaId' => $id], ['id' => $this->getId()]
			);
			
			$area->getPlace()->joinMember($conn, $this->requestor, $resolver->getHighestRole('Area'), false, '');
			return $area;
		}
		return false;
	}
	
	public function revoke(Connection $conn)
	{
		$this->status = $conn->fetchColumn('SELECT `status` FROM `'.CoreTables::AREA_REQUEST_TBL.'` WHERE `id` = :id', [':id' => $this->id]);
		if ($this->status == self::STATUS_VERIFICATION) {
			$this->lastUpdatedAt = time();
			$this->status = self::STATUS_REVOKED;

			$conn->update(CoreTables::AREA_REQUEST_TBL,
				['lastUpdatedAt' => $this->lastUpdatedAt, 'status' => $this->status],
				['id' => $this->getId()]
			);
			return true;
		}
		return false;
	}
	
	public function getFeedback(Connection $conn, TimeFormatterInterface $timeFormatter)
	{
		$stmt = $conn->prepare('SELECT m.`createdAt`, m.`message`, u.`id` AS `user_id`, u.`name`, u.`avatar` FROM `'.CoreTables::AREA_REQUEST_COMMENT_TBL.'` m '
			. 'INNER JOIN `'.CoreTables::USER_TBL.'` u ON u.`id` = m.`userId` '
			. 'WHERE m.`requestId` = :id ORDER BY m.`id`');
		$stmt->bindValue(':id', $this->id);
		$stmt->execute();
		$result = [];
		$direction = 1;
		$previousUser = null;
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($previousUser != $row['user_id']) {
				$direction = ($direction == 1 ? 0 : 1);
			}
			$result[] = [
				'message' => $row['message'],
				'time' => $timeFormatter->ago($row['createdAt']),
				'author' => $row['name'],
				'avatar' => $row['avatar'],
				'dir' => $direction
			];
			$previousUser = $row['user_id'];
		}
		$stmt->closeCursor();
		return $result;
	}
}