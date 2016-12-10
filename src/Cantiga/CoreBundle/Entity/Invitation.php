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

use Cantiga\CoreBundle\CoreTables;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\QueryClause;
use Doctrine\DBAL\Connection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Invitations are a way to join to the existing project, group or area. The manager of the place can
 * send an invitation to the specified e-mail address. The owner of the mailbox - if he has an account -
 * will see the invitation in his/her profile and can accept it or reject. When the invitation is
 * accepted, the user joins the given place. If the user does not have an account yet, he/she must
 * create one and then the invitation will be linked to the newly created profile.
 */
class Invitation implements IdentifiableInterface, InsertableEntityInterface, RemovableEntityInterface
{
	private $id;
	private $email;
	private $user;
	private $role;
	private $note;
	private $showDownstreamContactData;
	private $place;
	private $inviter;
	private $createdAt;
	private $assignmentKey;
	
	public static function fetchByUser(Connection $conn, $id, User $user)
	{
		$data = $conn->fetchAssoc('SELECT i.*, p.`id` AS `place_id`, p.`name` AS `place_name`, p.`type` AS `place_type`, p.`slug` AS `place_slug`, '
			. 'p.`removedAt` AS `place_removedAt`, p.`memberNum` AS `place_memberNum`, p.`rootPlaceId` AS `place_rootPlaceId` '
			. 'FROM `'.CoreTables::INVITATION_TBL.'` i '
			. 'INNER JOIN `'.CoreTables::PLACE_TBL.'` p ON p.`id` = i.`placeId` '
			. 'WHERE i.`id` = :id AND i.`userId` = :userId', [':id' => $id, ':userId' => $user->getId()]);
		if (empty($data)) {
			return false;
		}
		$item = self::fromArray($data);
		$item->user = $user;
		$item->place = Place::fromArray($data, 'place');
		return $item;
	}
	
	public static function fetchByKey(Connection $conn, $key)
	{
		$data = $conn->fetchAssoc('SELECT * '
			. 'FROM `'.CoreTables::INVITATION_TBL.'` WHERE `assignmentKey` = :key AND `userId` IS NULL', [':key' => $key]);
		if (empty($data)) {
			return false;
		}
		$item = self::fromArray($data);
		$item->user = null;
		return $item;
	}
	
	public static function fromArray($array, $prefix = '')
	{
		$item = new Invitation;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public static function getRelationships()
	{
		return ['place'];
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('email', new NotBlank());
		$metadata->addPropertyConstraint('email', new Length(array('min' => 2, 'max' => 100)));
		$metadata->addPropertyConstraint('email', new Email());
		$metadata->addPropertyConstraint('note', new NotBlank());
		$metadata->addPropertyConstraint('note', new Length(array('min' => 2, 'max' => 30)));
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getEmail()
	{
		return $this->email;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function getRole()
	{
		return $this->role;
	}

	public function getNote(): string
	{
		return (string) $this->note;
	}
	
	public function getShowDownstreamContactData(): bool
	{
		return (bool) $this->showDownstreamContactData;
	}

	/**
	 * @return Place
	 */
	public function getPlace()
	{
		return $this->place;
	}

	public function getInviter()
	{
		return $this->inviter;
	}

	public function getCreatedAt()
	{
		return $this->createdAt;
	}
	
	/**
	 * Unique, 40-character key of the invitation. It is sent together with the invitation to a person
	 * who does not have an account yet. The person may want to create an account with a different e-mail
	 * address and the unique key can be used to bind the original invitation to the new profile.
	 * 
	 * @return string
	 */
	public function getAssignmentKey()
	{
		return $this->assignmentKey;
	}
	
	public function setId($id): self
	{
		DataMappers::noOverwritingId($this->id);
		$this->id = $id;
		return $this;
	}

	public function setEmail($email): self
	{
		$this->email = $email;
		return $this;
	}

	public function setUser(User $user): self
	{
		$this->user = $user;
		return $this;
	}

	public function setRole($role): self
	{
		$this->role = (int) $role;
		return $this;
	}

	public function setNote($note): self
	{
		$this->note = $note;
		return $this;
	}
	
	public function setShowDownstreamContactData($showDownstreamContactData): self
	{
		$this->showDownstreamContactData = (bool) $showDownstreamContactData;
		return $this;
	}

	public function setPlace(Place $place): self
	{
		$this->place = $place;
		return $this;
	}

	public function setInviter(User $inviter): self
	{
		$this->inviter = $inviter;
		return $this;
	}

	public function setCreatedAt($createdAt): self
	{
		$this->createdAt = (int) $createdAt;
		return $this;
	}

	public function insert(Connection $conn)
	{
		$this->createdAt = time();
		$this->assignmentKey = strtoupper(hash('sha256', uniqid(time().'LWDXFDF'.$_SERVER['REMOTE_ADDR'].rand(-2000000, 2000000).'djdjDfjashaXms')));
		$checkUser = User::fetchByCriteria($conn, QueryClause::clause('u.`email` = :email', ':email', $this->email));
		if (false === $checkUser) {
			$this->id = $conn->insert(CoreTables::INVITATION_TBL, [
				'email' => $this->email,
				'role' => $this->role,
				'note' => $this->note,
				'showDownstreamContactData' => (int) $this->showDownstreamContactData,
				'placeId' => $this->place->getId(),
				'inviterId' => $this->inviter->getId(),
				'createdAt' => $this->createdAt,
				'assignmentKey' => $this->assignmentKey				
			]);
		} else {
			$this->user = $checkUser;
			$this->id = $conn->insert(CoreTables::INVITATION_TBL, [
				'email' => $this->email,
				'userId' => $this->user->getId(),
				'role' => $this->role,
				'note' => $this->note,
				'showDownstreamContactData' => (int) $this->showDownstreamContactData,
				'placeId' => $this->place->getId(),
				'inviterId' => $this->inviter->getId(),
				'createdAt' => $this->createdAt,
				'assignmentKey' => $this->assignmentKey		
			]);
		}
		return $this->id;
	}
	
	public function join(Connection $conn, User $user)
	{
		$this->user = $user;
		$conn->update(CoreTables::INVITATION_TBL, ['userId' => $user->getId()], ['id' => $this->id]);
	}

	public function canRemove()
	{
		return true;
	}

	public function remove(Connection $conn)
	{
		$conn->delete(CoreTables::INVITATION_TBL, ['id' => $this->id]);
	}
}
