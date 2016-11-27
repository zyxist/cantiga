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
use Cantiga\Components\Hierarchy\Entity\MembershipRole;
use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\Components\Hierarchy\MembershipEntityInterface;
use Cantiga\Components\Hierarchy\MembershipRoleResolverInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Traits\EntityTrait;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\Join;
use Cantiga\Metamodel\Membership;
use Cantiga\Metamodel\QueryClause;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Group implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface, MembershipEntityInterface, HierarchicalInterface
{
	use EntityTrait;
	
	private $id;
	private $name;
	private $slug;
	private $project;
	private $category;
	private $notes;
	private $memberNum;
	private $areaNum;
	
	public static function fetchByProject(Connection $conn, $id, Project $project)
	{
		$data = $conn->fetchAssoc('SELECT g.*, '
			. self::createEntityFieldList()
			. 'FROM `'.CoreTables::GROUP_TBL.'` g '
			. self::createEntityJoin('g')
			. 'WHERE g.`id` = :id AND g.`projectId` = :projectId', [':id' => $id, ':projectId' => $project->getId()]);
		if(null === $data) {
			return false;
		}
		$item = Group::fromArray($data);
		$item->project = $project;
		
		if (!empty($data['categoryId'])) {
			$item->category = GroupCategory::fetchByProject($conn, $data['categoryId'], $project);
		}
		$item->entity = Entity::fromArray($data, 'entity');
		
		return $item;
	}
	
	public static function fetch(Connection $conn, $id)
	{
		$data = $conn->fetchAssoc('SELECT g.*, '
			. self::createEntityFieldList()
			. 'FROM `'.CoreTables::GROUP_TBL.'` g '
			. self::createEntityJoin('g')
			. 'WHERE g.`id` = :id', [':id' => $id]);
		if(null === $data) {
			return false;
		}
		$item = Group::fromArray($data);
		$item->project = Project::fetchActive($conn, $data['projectId']);
		if (false === $item->project) {
			return false;
		}
		
		if (!empty($data['categoryId'])) {
			$item->category = GroupCategory::fetchByProject($conn, $data['categoryId'], $item->project);
		}
		$item->entity = Entity::fromArray($data, 'entity');
		
		return $item;
	}
	
	/**
	 * @param Connection $conn
	 * @param int $projectId
	 * @param int $userId
	 * @return Membership
	 */
	public static function fetchMembership(Connection $conn, MembershipRoleResolverInterface $resolver, $slug, $userId)
	{
		$data = $conn->fetchAssoc('SELECT g.*, '
			. 'm.`role` AS `membership_role`, m.`note` AS `membership_note`, '
			. self::createEntityFieldList()
			. 'FROM `'.CoreTables::GROUP_TBL.'` g '
			. self::createEntityJoin('g')
			. 'INNER JOIN `'.CoreTables::GROUP_MEMBER_TBL.'` m ON m.`groupId` = g.`id` '
			. 'WHERE m.`userId` = :userId AND g.`slug` = :slug', [':userId' => $userId, ':slug' => $slug]);
		if(false === $data) {
			return false;
		}
		$group = self::fromArray($data);
		$group->project = Project::fetchActive($conn, $data['projectId']);
		if (false == $group->project) {
			return false;
		}
		
		if (!empty($data['categoryId'])) {
			$group->category = GroupCategory::fetchByProject($conn, $data['categoryId'], $group->project);
		}
		$group->entity = Entity::fromArray($data, 'entity');
		
		$role = $resolver->getRole('Group', $data['membership_role']);
		return new Membership($group, $role, $data['membership_note']);
	}

	public static function fromArray($array, $prefix = '')
	{
		$item = new Group;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public static function getRelationships()
	{
		return ['project', 'category', 'entity'];
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('name', new NotBlank());
		$metadata->addPropertyConstraint('name', new Length(array('min' => 2, 'max' => 500)));
		$metadata->addPropertyConstraint('notes', new Length(array('max' => 500)));
	}
	
	public function getTypeName():string
	{
		return 'Group';
	}
	
	public function isRoot(): bool
	{
		return false;
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
	
	public function getSlug()
	{
		return $this->slug;
	}

	public function setSlug($slug)
	{
		$this->slug = $slug;
		return $this;
	}
	
	public function getProject()
	{
		return $this->project;
	}
	
	public function setProject(Project $project)
	{
		$this->project = $project;
		return $this;
	}
	
	public function getCategory()
	{
		return $this->category;
	}

	public function getNotes()
	{
		return $this->notes;
	}

	public function setCategory($category)
	{
		$this->category = $category;
		return $this;
	}

	public function setNotes($notes)
	{
		$this->notes = $notes;
		return $this;
	}
	
	public function getMemberNum()
	{
		return $this->memberNum;
	}

	public function getAreaNum()
	{
		return $this->areaNum;
	}

	public function setMemberNum($memberNum)
	{
		$this->memberNum = $memberNum;
		return $this;
	}

	public function setAreaNum($areaNum)
	{
		$this->areaNum = $areaNum;
		return $this;
	}
	
	public function insert(Connection $conn)
	{
		$this->slug = DataMappers::generateSlug($conn, CoreTables::GROUP_TBL);
		
		$this->entity = new Entity();
		$this->entity->setType('Group');
		$this->entity->setName($this->name);
		$this->entity->setSlug($this->slug);
		$this->entity->insert($conn);
		
		$conn->insert(
			CoreTables::GROUP_TBL,
			DataMappers::pick($this, ['name', 'slug', 'project', 'category', 'notes', 'entity'])
		);
		return $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		$conn->executeQuery('UPDATE `'.CoreTables::AREA_TBL.'` SET `groupName` = :groupName WHERE `groupId` = :id', [
			':groupName' => $this->name,
			':id' => $this->id
		]);
		$this->entity->setName($this->name);
		$this->entity->update($conn);
		
		return $conn->update(
			CoreTables::GROUP_TBL,
			DataMappers::pick($this, ['name', 'category', 'notes']),
			DataMappers::pick($this, ['id'])
		);
	}
	
	public function canRemove()
	{
		return $this->areaNum == 0;
	}
	
	public function remove(Connection $conn)
	{
		if ($this->canRemove()) {
			$this->entity->remove($conn);
			$conn->delete(CoreTables::GROUP_TBL, DataMappers::pick($this, ['id']));
		}
	}
	
	/**
	 * Finds the hints for the users that could join the project, basing on their partial e-mail
	 * address.
	 * 
	 * @param string $mailQuery
	 * @return array
	 */
	public function findHints(Connection $conn, $mailQuery)
	{
		$mailQuery = trim(str_replace('%', '', $mailQuery));
		if (strlen($mailQuery) < 3) {
			return array();
		}
		
		$items = $conn->fetchAll('SELECT `email` FROM `'.CoreTables::USER_TBL.'` WHERE '
			. '`email` LIKE :email AND `id` NOT IN(SELECT `userId` FROM `'.CoreTables::GROUP_MEMBER_TBL.'` WHERE `groupId` = :group) ORDER BY `email` DESC LIMIT 15', [':group' => $this->getId(), ':email' => $mailQuery.'%']);
		if (!empty($items)) {
			$result = array();
			foreach ($items as $item) {
				$result[] = $item['email'];
			}
			return $result;
		}
		return array();
	}
	
	/**
	 * Displays the information about the group members for members of the given other entity.
	 * 
	 * @param Connection $conn
	 * @param MembershipEntityInterface $entity Another entity that views the information about members.
	 * @return array
	 */
	public function findMemberInformationForEntity(Connection $conn, MembershipEntityInterface $entity)
	{
		$stmt = $conn->prepare('SELECT u.id, u.name, u.avatar, u.lastVisit, p.location, p.telephone, p.publicMail, p.privShowTelephone, p.privShowPublicMail, m.note '
			. 'FROM `'.CoreTables::USER_TBL.'` u '
			. 'INNER JOIN `'.CoreTables::USER_PROFILE_TBL.'` p ON p.`userId` = u.`id` '
			. 'INNER JOIN `'.CoreTables::GROUP_MEMBER_TBL.'` m ON m.`userId` = u.`id` '
			. 'WHERE m.`groupId` = :groupId ORDER BY m.role DESC, u.name');
		$stmt->bindValue(':groupId', $this->getId());
		$stmt->execute();
		$result = array();
		
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$row['privShowTelephone'] = User::evaluateUserPrivacy($row['privShowTelephone'], $entity);
			$row['privShowPublicMail'] = User::evaluateUserPrivacy($row['privShowPublicMail'], $entity);
			
			$result[] = $row;
		}
		$stmt->closeCursor();
		return $result;
	}
	
	public function findAreaSummary(Connection $conn)
	{
		$stmt = $conn->prepare('SELECT a.id, a.name, s.name AS `statusText` '
			. 'FROM `'.CoreTables::AREA_TBL.'` a '
			. 'INNER JOIN `'.CoreTables::AREA_STATUS_TBL.'` s ON s.`id` = a.`statusId` '
			. 'WHERE a.`groupId` = :groupId ORDER BY a.name');
		$stmt->bindValue(':groupId', $this->getId());
		$stmt->execute();
		$result = array();
		
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$result[] = $row;
		}
		$stmt->closeCursor();
		return $result;
	}
	
	public function findMembers(Connection $conn, MembershipRoleResolverInterface $roleResolver): array
	{
		$stmt = $conn->prepare('SELECT i.`id`, i.`name`, i.`avatar`, i.`lastVisit`, p.`location`, c.`email` AS `contactMail`, '
			. 'c.`telephone` AS `contactTelephone`, c.`notes` AS `notes`, m.`role` AS `membershipRole`, m.`note` AS `membershipNote` '
			. 'FROM `'.CoreTables::USER_TBL.'` i '
			. 'INNER JOIN `'.CoreTables::USER_PROFILE_TBL.'` p ON p.`userId` = i.`id` '
			. 'INNER JOIN `'.CoreTables::GROUP_MEMBER_TBL.'` m ON m.`userId` = i.`id` '
			. 'LEFT JOIN `'.CoreTables::CONTACT_TBL.'` c ON c.`userId` = i.`id` AND c.`projectId` = :projectId '
			. 'WHERE m.`groupId` = :entityId AND i.`active` = 1 AND i.`removed` = 0 '
			. 'ORDER BY i.`name`');
		$stmt->bindValue(':projectId', $this->getProject()->getId());
		$stmt->bindValue(':entityId', $this->getId());
		$stmt->execute();
		$results = [];
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$results[] = new Member(
				$row, new Membership($this, $roleResolver->getRole('Group', $row['membershipRole']), $row['membershipNote'])
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
			. 'INNER JOIN `'.CoreTables::GROUP_MEMBER_TBL.'` m ON m.`userId` = i.`id` '
			. 'LEFT JOIN `'.CoreTables::CONTACT_TBL.'` c ON c.`userId` = i.`id` AND c.`projectId` = :projectId '
			. 'WHERE m.`groupId` = :entityId AND i.`active` = 1 AND i.`removed` = 0 AND i.`id` = :userId '
			. 'ORDER BY i.`name`');
		$stmt->bindValue(':projectId', $this->getProject()->getId());
		$stmt->bindValue(':entityId', $this->getId());
		$stmt->bindValue(':userId', $id);
		$stmt->execute();
		$results = [];
		if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$stmt->closeCursor();
			return new Member(
				$row, new Membership($this, $resolver->getRole('Group', $row['membershipRole']), $row['membershipNote'])
			);
		}
		$stmt->closeCursor();
		return false;
	}

	public function joinMember(Connection $conn, User $user, MembershipRole $role, $note)
	{
		$ifExists = $conn->fetchColumn('SELECT `userId` FROM `'.CoreTables::GROUP_MEMBER_TBL.'` WHERE `groupId` = :group AND `userId` = :user', [':group' => $this->getId(), ':user' => $user->getId()]);
		if (false === $ifExists) {
			$conn->insert(CoreTables::GROUP_MEMBER_TBL, ['groupId' => $this->getId(), 'userId' => $user->getId(), 'role' => $role->getId(), 'note' => $note]);
			$conn->executeQuery('UPDATE `'.CoreTables::GROUP_TBL.'` SET `memberNum` = (`memberNum` + 1) WHERE `id` = :id', [':id' => $this->id]);
			$conn->executeQuery('UPDATE `'.CoreTables::USER_TBL.'` SET `groupNum` = (`groupNum` + 1) WHERE `id` = :id', [':id' => $user->getId()]);
			return true;
		}
		return false;
	}
	
	public function editMember(Connection $conn, User $user, MembershipRole $role, $note)
	{
		return 1 == $conn->update(CoreTables::GROUP_MEMBER_TBL, ['role' => (int) $role->getId(), 'note' => $note], ['groupId' => $this->getId(), 'userId' => $user->getId()]);
	}

	public function removeMember(Connection $conn, User $user)
	{
		if (1 == $conn->delete(CoreTables::GROUP_MEMBER_TBL, ['groupId' => $this->getId(), 'userId' => $user->getId()])) {
			$conn->executeQuery('UPDATE `'.CoreTables::GROUP_TBL.'` SET `memberNum` = (`memberNum` - 1) WHERE `id` = :id', [':id' => $this->id]);
			$conn->executeQuery('UPDATE `'.CoreTables::USER_TBL.'` SET `groupNum` = (`groupNum` - 1) WHERE `id` = :id', [':id' => $user->getId()]);
			return true;
		}
		return false;
	}

	public function getElementOfType(int $type)
	{
		if ($type == HierarchicalInterface::TYPE_PROJECT) {
			return $this->getProject();
		} elseif ($type == HierarchicalInterface::TYPE_GROUP) {
			return $this;
		}
		return null;
	}

	public function getParents(): array
	{
		return [$this->project];
	}

	public function getRootElement(): HierarchicalInterface
	{
		return $this->getProject();
	}
}