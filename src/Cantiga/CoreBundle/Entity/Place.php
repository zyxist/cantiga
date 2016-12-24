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

use Cantiga\Components\Hierarchy\Entity\Member;
use Cantiga\Components\Hierarchy\Entity\MemberInfo;
use Cantiga\Components\Hierarchy\Entity\MembershipRole;
use Cantiga\Components\Hierarchy\MembershipEntityInterface;
use Cantiga\Components\Hierarchy\MembershipRoleResolverInterface;
use Cantiga\Components\Hierarchy\PlaceRefInterface;
use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\UserBundle\UserTables;
use Doctrine\DBAL\Connection;
use PDO;

/**
 * Generic database representation of any place. We create such a row in order
 * to support functionalities that can be bound to the places of different types.
 * For example, the task list can be bound both to group and area. Each place that
 * should be managed in this way, should also deal with creating and managing such a
 * row.
 */
class Place implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface, MembershipEntityInterface, PlaceRefInterface
{
	private $id;
	private $name;
	private $slug = '';
	private $type;
	private $removedAt;
	private $memberNum;
	private $rootPlaceId;
	private $archived = false;
	
	private $pendingArchivization = false;
	
	public static function fetchById(Connection $conn, $id)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.CoreTables::PLACE_TBL.'` WHERE `id` = :id', [':id' => $id]);
		if (false === $data) {
			return false;
		}
		return Place::fromArray($data);
	}
	
	public static function fetchBySlug(Connection $conn, $slug)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.CoreTables::PLACE_TBL.'` WHERE `slug` = :slug', [':slug' => $slug]);
		if (false === $data) {
			return false;
		}
		return Place::fromArray($data);
	}
	
	public static function fromArray($array, $prefix = '')
	{
		$item = new Place;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setId($id): self
	{
		DataMappers::noOverwritingId($this->id);
		$this->id = $id;
		return $this;
	}

	public function setName($name): self
	{
		$this->name = $name;
		return $this;
	}
	
	function getSlug(): string
	{
		return $this->slug;
	}

	function setSlug($slug): self
	{
		$this->slug = $slug;
		return $this;
	}

	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	
	public function getRemovedAt()
	{
		return $this->removedAt;
	}

	public function setRemovedAt($removedAt): self
	{
		$this->removedAt = $removedAt;
		return $this;
	}
	
	public function getMemberNum()
	{
		return $this->memberNum;
	}
	
	public function isRoot(): bool
	{
		return null !== $this->rootPlaceId;
	}

	public function getRootPlaceId()
	{
		return $this->rootPlaceId;
	}

	public function setMemberNum($memberNum): self
	{
		$this->memberNum = $memberNum;
		return $this;
	}

	public function setRootPlaceId($rootPlaceId): self
	{
		$this->rootPlaceId = $rootPlaceId;
		return $this;
	}
	
	public function getArchived(): bool
	{
		return (bool) $this->archived;
	}
	
	public function setArchived($archived)
	{
		$this->archived = (bool) $archived;
		return $this;
	}
	
	public function archivize()
	{
		if (!$this->archived) {
			$this->archived = true;
			$this->pendingArchivization = true;
		}
	}

	public function canRemove()
	{
		return true;
	}

	public function insert(Connection $conn)
	{
		$conn->insert(
			CoreTables::PLACE_TBL,
			DataMappers::pick($this, ['name', 'slug', 'type', 'rootPlaceId'])
		);
		return $this->id = $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		if ($this->pendingArchivization) {
			$conn->executeUpdate('UPDATE `'.CoreTables::PLACE_TBL.'` SET `archived` = 1 WHERE `rootPlaceId` = :rootPlaceId', [':rootPlaceId' => $this->getId()]);
			return $conn->update(
				CoreTables::PLACE_TBL,
				DataMappers::pick($this, ['name', 'archived']),
				DataMappers::pick($this, ['id'])
			);
		} else {
			return $conn->update(
				CoreTables::PLACE_TBL,
				DataMappers::pick($this, ['name']),
				DataMappers::pick($this, ['id'])
			);
		}
	}
	
	public function remove(Connection $conn)
	{
		$this->removedAt = time();
		return $conn->update(
			CoreTables::PLACE_TBL,
			DataMappers::pick($this, ['removedAt']),
			DataMappers::pick($this, ['id'])
		);
	}
	
	/**
	 * Finds the hints for the users that could join the project, basing on their partial e-mail
	 * address.
	 * 
	 * @param string $mailQuery
	 * @return array
	 */
	public function findHints(Connection $conn, string $mailQuery): array
	{
		$mailQuery = trim(str_replace('%', '', $mailQuery));
		if (strlen($mailQuery) < 3) {
			return array();
		}
		
		$items = $conn->fetchAll('SELECT `email` FROM `'.CoreTables::USER_TBL.'` WHERE '
			. '`email` LIKE :email AND `id` NOT IN(SELECT `userId` FROM `'.UserTables::PLACE_MEMBERS_TBL.'` WHERE `placeId` = :placeId) AND `active` = 1 AND `removed` = 0 ORDER BY `email` DESC LIMIT 15', [':placeId' => $this->getId(), ':email' => $mailQuery.'%']);
		if (!empty($items)) {
			$result = array();
			foreach ($items as $item) {
				$result[] = $item['email'];
			}
			return $result;
		}
		return array();
	}
	
	public function findMembers(Connection $conn, MembershipRoleResolverInterface $roleResolver): array
	{
		$stmt = $conn->prepare('SELECT i.`id`, i.`name`, i.`avatar`, i.`lastVisit`, p.`location`, c.`email` AS `contactMail`, '
			. 'c.`telephone` AS `contactTelephone`, c.`notes` AS `notes`, m.`role` AS `membershipRole`, m.`note` AS `membershipNote`, m.`showDownstreamContactData` '
			. 'FROM `'.CoreTables::USER_TBL.'` i '
			. 'INNER JOIN `'.CoreTables::USER_PROFILE_TBL.'` p ON p.`userId` = i.`id` '
			. 'INNER JOIN `'.UserTables::PLACE_MEMBERS_TBL.'` m ON m.`userId` = i.`id` '
			. 'LEFT JOIN `'.CoreTables::CONTACT_TBL.'` c ON c.`userId` = i.`id` AND c.`placeId` = :placeId1 '
			. 'WHERE m.`placeId` = :placeId2 AND i.`active` = 1 AND i.`removed` = 0 '
			. 'ORDER BY i.`name`');
		if ($this->type == 'Project') {
			$stmt->bindValue(':placeId1', $this->getId());
		} else {
			$stmt->bindValue(':placeId1', $this->rootPlaceId);
		}
		$stmt->bindValue(':placeId2', $this->getId());
		$stmt->execute();
		$results = [];
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$results[] = new Member(
				$row, new MemberInfo($roleResolver->getRole($this->getType(), $row['membershipRole']), $row['membershipNote'], (bool) $row['showDownstreamContactData'])
			);
		}
		$stmt->closeCursor();
		return $results;
	}
	
	public function findMember(Connection $conn, MembershipRoleResolverInterface $resolver, int $id)
	{
		$stmt = $conn->prepare('SELECT i.`id`, i.`name`, i.`avatar`, i.`lastVisit`, p.`location`, c.`email` AS `contactMail`, '
			. 'c.`telephone` AS `contactTelephone`, c.`notes` AS `notes`, m.`role` AS `membershipRole`, m.`note` AS `membershipNote` '
			. 'FROM `'.CoreTables::USER_TBL.'` i '
			. 'INNER JOIN `'.CoreTables::USER_PROFILE_TBL.'` p ON p.`userId` = i.`id` '
			. 'INNER JOIN `'.UserTables::PLACE_MEMBERS_TBL.'` m ON m.`userId` = i.`id` '
			. 'LEFT JOIN `'.CoreTables::CONTACT_TBL.'` c ON c.`userId` = i.`id` AND c.`placeId` = :placeId1 '
			. 'WHERE m.`placeId` = :placeId2 AND i.`active` = 1 AND i.`removed` = 0 AND i.`id` = :userId '
			. 'ORDER BY i.`name`');
		$stmt->bindValue(':placeId1', $this->rootPlaceId);
		$stmt->bindValue(':placeId2', $this->getId());
		$stmt->bindValue(':userId', $id);
		$stmt->execute();
		$results = [];
		if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$stmt->closeCursor();
			return new Member(
				$row, new MemberInfo($resolver->getRole($this->getType(), $row['membershipRole']), $row['membershipNote'])
			);
		}
		$stmt->closeCursor();
		return false;
	}

	public function joinMember(Connection $conn, CantigaUserRefInterface $user, MembershipRole $role, $note, $showDownstreamContactData)
	{
		$ifExists = $conn->fetchColumn('SELECT `userId` FROM `'.UserTables::PLACE_MEMBERS_TBL.'` WHERE `placeId` = :placeId AND `userId` = :user FOR UPDATE', [':placeId' => $this->getId(), ':user' => $user->getId()]);
		if (false === $ifExists) {
			$conn->insert(UserTables::PLACE_MEMBERS_TBL, [
				'placeId' => $this->getId(),
				'userId' => $user->getId(),
				'role' => $role->getId(),
				'showDownstreamContactData' => $showDownstreamContactData,
				'note' => $note
			]);
			$this->updateCounters($conn, $user);
			return true;
		}
		return false;
	}
	
	public function editMember(Connection $conn, CantigaUserRefInterface $user, MembershipRole $role, $note, $showDownstreamContactData)
	{
		return 1 == $conn->update(UserTables::PLACE_MEMBERS_TBL, [
			'role' => (int) $role->getId(),
			'showDownstreamContactData' => $showDownstreamContactData,
			'note' => $note
		], ['placeId' => $this->getId(), 'userId' => $user->getId()]);
	}

	public function removeMember(Connection $conn, CantigaUserRefInterface $user)
	{
		if (1 == $conn->delete(UserTables::PLACE_MEMBERS_TBL, ['placeId' => $this->getId(), 'userId' => $user->getId()])) {
			
			return true;
		}
		return false;
	}
	
	private function updateCounters(Connection $conn, User $user)
	{
		$conn->executeQuery('UPDATE `'.CoreTables::PLACE_TBL.'` SET `memberNum` = (SELECT COUNT(`userId`) FROM `'.UserTables::PLACE_MEMBERS_TBL.'` WHERE `placeId` = :placeId1) WHERE `id` = :placeId2', [
			':placeId1' => $this->id,
			':placeId2' => $this->id,
		]);
		$conn->executeQuery('UPDATE `'.CoreTables::USER_TBL.'` SET `placeNum` = (SELECT COUNT(`placeId`) FROM `'.UserTables::PLACE_MEMBERS_TBL.'` WHERE `userId` = :userId1) WHERE `id` = :userId2', [
			':userId1' => $user->getId(),
			':userId2' => $user->getId(),
		]);
	}
}
