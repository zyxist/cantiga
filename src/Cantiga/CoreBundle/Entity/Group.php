<?php
namespace Cantiga\CoreBundle\Entity;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\MembershipEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\Join;
use Cantiga\Metamodel\Membership;
use Cantiga\Metamodel\MembershipRole;
use Cantiga\Metamodel\MembershipRoleResolver;
use Cantiga\Metamodel\QueryClause;
use Doctrine\DBAL\Connection;
use PDO;

class Group implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface, MembershipEntityInterface
{
	private $id;
	private $name;
	private $slug;
	private $project;
	private $memberNum;
	private $areaNum;
	
	public static function fetchByProject(Connection $conn, $id, Project $project)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.CoreTables::GROUP_TBL.'` WHERE `id` = :id AND `projectId` = :projectId', [':id' => $id, ':projectId' => $project->getId()]);
		if(null === $data) {
			return false;
		}
		$item = Group::fromArray($data);
		$item->project = $project;
		return $item;
	}
	
	public static function fetch(Connection $conn, $id)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.CoreTables::GROUP_TBL.'` WHERE `id` = :id', [':id' => $id]);
		if(null === $data) {
			return false;
		}
		$item = Group::fromArray($data);
		$item->project = Project::fetchActive($conn, $data['projectId']);
		if (false === $item->project) {
			return false;
		}
		return $item;
	}
	
	/**
	 * @param Connection $conn
	 * @param int $projectId
	 * @param int $userId
	 * @return Membership
	 */
	public static function fetchMembership(Connection $conn, MembershipRoleResolver $resolver, $slug, $userId)
	{
		$data = $conn->fetchAssoc('SELECT g.*, m.`role` AS `membership_role`, m.`note` AS `membership_note` FROM `'.CoreTables::GROUP_TBL.'` g '
			. 'INNER JOIN `'.CoreTables::GROUP_MEMBER_TBL.'` m ON m.`groupId` = g.`id` WHERE m.`userId` = :userId AND g.`slug` = :slug', [':userId' => $userId, ':slug' => $slug]);
		if(false === $data) {
			return false;
		}
		$group = self::fromArray($data);
		$group->project = Project::fetchActive($conn, $data['projectId']);
		if (false == $group->project) {
			return false;
		}
		
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
		return ['project'];
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
		$conn->insert(
			CoreTables::GROUP_TBL,
			DataMappers::pick($this, ['name', 'slug', 'project'])
		);
		return $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		$conn->executeQuery('UPDATE `'.CoreTables::AREA_TBL.'` SET `groupName` = :groupName WHERE `groupId` = :id', [
			':groupName' => $this->name,
			':id' => $this->id
		]);
		return $conn->update(
			CoreTables::GROUP_TBL,
			DataMappers::pick($this, ['name', 'slug', 'project']),
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
	
	public function findMembers(Connection $conn, MembershipRoleResolver $roleResolver)
	{
		$stmt = $conn->prepare('SELECT u.id, u.name, p.role, p.note FROM `'.CoreTables::USER_TBL.'` u '
			. 'INNER JOIN `'.CoreTables::GROUP_MEMBER_TBL.'` p ON p.`userId` = u.`id` WHERE p.`groupId` = :groupId ORDER BY p.role DESC, u.name');
		$stmt->bindValue(':groupId', $this->getId());
		$stmt->execute();
		$result = array();
		
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$role = $roleResolver->getRole('Group', $row['role']);
			$row['roleName'] = $role->getName();
			$result[] = $row;
		}
		$stmt->closeCursor();
		return $result;
	}
	
	public function findMember(Connection $conn, MembershipRoleResolver $resolver, $id)
	{
		return User::fetchLinkedProfile(
			$conn, $resolver, $this,
			Join::create(CoreTables::GROUP_MEMBER_TBL, 'm', QueryClause::clause('m.`userId` = u.`id`')),
			QueryClause::clause('u.`id` = :id', ':id', $id)
		);
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
}