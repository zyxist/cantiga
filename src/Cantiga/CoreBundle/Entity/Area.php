<?php
namespace Cantiga\CoreBundle\Entity;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\MembershipEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\Join;
use Cantiga\Metamodel\Membership;
use Cantiga\Metamodel\MembershipRole;
use Cantiga\Metamodel\MembershipRoleResolver;
use Cantiga\Metamodel\QueryClause;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Area implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, MembershipEntityInterface
{
	private $id;
	private $name;
	private $slug;
	private $project;
	private $status;
	private $group;
	private $territory;
	private $reporter;
	private $memberNum;
	private $customData;
	
	private $oldGroup;
	private $oldStatus;
	private $oldTerritory;
	
	public static function newArea(Project $project, Territory $territory, AreaStatus $status, $name)
	{
		$item = new Area();
		$item->setProject($project);
		$item->setTerritory($territory);
		$item->setStatus($status);
		$item->setName($name);
		return $item;
	}

	public static function fetchActive(Connection $conn, $id)
	{
		$data = $conn->fetchAssoc('SELECT a.*, '
			. 't.`id` AS `territory_id`, t.`name` AS `territory_name`, t.`areaNum` AS `territory_areaNum`, t.`requestNum` as `territory_requestNum` '
			. 'FROM `'.CoreTables::AREA_TBL.'` a '
			. 'INNER JOIN `'.CoreTables::TERRITORY_TBL.'` t ON t.`id` = a.`territoryId` '
			. 'INNER JOIN `'.CoreTables::PROJECT_TBL.'` p ON p.`id` = a.`projectId` WHERE a.`id` = :id AND p.`archived` = 0', [':id' => $id]);
		if(null === $data) {
			return false;
		}
		$item = Area::fromArray($data);
		$item->project = Project::fetchActive($conn, $data['projectId']);
		if (false == $item->project) {
			return false;
		}
		
		$item->status = $item->oldStatus = AreaStatus::fetchByProject($conn, $data['statusId'], $item->project);
		$item->setTerritory($item->oldTerritory = Territory::fromArray($data, 'territory'));
		if (!empty($data['groupId'])) {
			$item->group = $item->oldGroup = Group::fetchByProject($conn, $data['groupId'], $item->project);
		}
		return $item;
	}
	
	public static function fetchByProject(Connection $conn, $id, Project $project)
	{
		$data = $conn->fetchAssoc('SELECT a.*, '
			. 't.`id` AS `territory_id`, t.`name` AS `territory_name`, t.`areaNum` AS `territory_areaNum`, t.`requestNum` as `territory_requestNum` '
			. 'FROM `'.CoreTables::AREA_TBL.'` a '
			. 'INNER JOIN `'.CoreTables::TERRITORY_TBL.'` t ON t.`id` = a.`territoryId` '
			. 'WHERE a.`id` = :id AND a.`projectId` = :projectId', [':id' => $id, ':projectId' => $project->getId()]);
		if(null === $data) {
			return false;
		}
		$item = Area::fromArray($data);
		$item->project = Project::fetchActive($conn, $data['projectId']);
		if (false == $item->project) {
			return false;
		}
		
		$item->status = $item->oldStatus = AreaStatus::fetchByProject($conn, $data['statusId'], $item->project);
		$item->setTerritory($item->oldTerritory = Territory::fromArray($data, 'territory'));
		if (!empty($data['groupId'])) {
			$item->group = $item->oldGroup = Group::fetchByProject($conn, $data['groupId'], $item->project);
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
		$data = $conn->fetchAssoc('SELECT a.*, '
			. 't.`id` AS `territory_id`, t.`name` AS `territory_name`, t.`areaNum` AS `territory_areaNum`, t.`requestNum` as `territory_requestNum`, '
			. 'm.`role` AS `membership_role`, m.`note` AS `membership_note` '
			. 'FROM `'.CoreTables::AREA_TBL.'` a '
			. 'INNER JOIN `'.CoreTables::TERRITORY_TBL.'` t ON t.`id` = a.`territoryId` '
			. 'INNER JOIN `'.CoreTables::AREA_MEMBER_TBL.'` m ON m.`areaId` = a.`id` WHERE m.`userId` = :userId AND a.`slug` = :slug', [':userId' => $userId, ':slug' => $slug]);
		if(false === $data) {
			return false;
		}
		$item = self::fromArray($data);
		$item->project = Project::fetchActive($conn, $data['projectId']);
		if (false == $item->project) {
			return false;
		}
		$item->status = $item->oldStatus = AreaStatus::fetchByProject($conn, $data['statusId'], $item->project);
		if (!empty($data['groupId'])) {
			$item->group = $item->oldGroup = Group::fetchByProject($conn, $data['groupId'], $item->project);
		}
		$item->setTerritory($item->oldTerritory = Territory::fromArray($data, 'territory'));
		$role = $resolver->getRole('Area', $data['membership_role']);
		return new Membership($item, $role, $data['membership_note']);
	}

	public static function fromArray($array, $prefix = '')
	{
		$item = new Area;
		if (!empty($array['customData'])) {
			$item->customData = json_decode($array['customData'], true);
			unset($array['customData']);
		} else {
			$item->customData = [];
			unset($array['customData']);
		}
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public static function getRelationships()
	{
		return ['project', 'status', 'group', 'territory', 'reporter'];
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

	public function setMemberNum($memberNum)
	{
		$this->memberNum = $memberNum;
		return $this;
	}
	
	public function getStatus()
	{
		return $this->status;
	}

	public function getGroup()
	{
		return $this->group;
	}

	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	public function setGroup(Group $group)
	{
		$this->group = $group;
		return $this;
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
	
	public function getCustomData()
	{
		return $this->customData;
	}

	public function setCustomData(array $customData)
	{
		$this->customData = $customData;
		return $this;
	}
	
	public function getReporter()
	{
		return $this->reporter;
	}

	public function setReporter($reporter)
	{
		$this->reporter = $reporter;
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

	public function insert(Connection $conn)
	{
		$this->status = AreaStatus::fetchDefault($conn, $this->project);
		$groupName = null;
		if (null !== $this->group) {
			$groupName = $this->group->getName();
		}
		
		if (null !== $this->group) {
			DataMappers::recount($conn, CoreTables::GROUP_TBL, null, $this->group, 'areaNum', 'id');
		}
		DataMappers::recount($conn, CoreTables::AREA_STATUS_TBL, null, $this->status, 'areaNum', 'id');
		DataMappers::recount($conn, CoreTables::TERRITORY_TBL, null, $this->territory, 'areaNum', 'id');
		
		$this->slug = DataMappers::generateSlug($conn, CoreTables::GROUP_TBL);
		$conn->insert(
			CoreTables::AREA_TBL,
			DataMappers::pick($this, ['name', 'slug', 'project', 'group', 'territory', 'status', 'reporter'], ['customData' => json_encode($this->customData), 'groupName' => $groupName])
		);
		return $this->id = $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		$groupName = null;
		if (null !== $this->group) {
			$groupName = $this->group->getName();
		}
		
		if (!DataMappers::same($this->oldGroup, $this->group)) {
			DataMappers::recount($conn, CoreTables::GROUP_TBL, $this->oldGroup, $this->group, 'areaNum', 'id');
		}
		if (!DataMappers::same($this->oldStatus, $this->status)) {
			DataMappers::recount($conn, CoreTables::AREA_STATUS_TBL, $this->oldStatus, $this->status, 'areaNum', 'id');
		}
		if (!DataMappers::same($this->oldTerritory, $this->territory)) {
			DataMappers::recount($conn, CoreTables::TERRITORY_TBL, $this->oldTerritory, $this->territory, 'areaNum', 'id');
		}
		
		return $conn->update(
			CoreTables::AREA_TBL,
			DataMappers::pick($this, ['name', 'group', 'territory', 'status'], ['customData' => json_encode($this->customData), 'groupName' => $groupName]),
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
	public function findHints(Connection $conn, $mailQuery)
	{
		$mailQuery = trim(str_replace('%', '', $mailQuery));
		if (strlen($mailQuery) < 3) {
			return array();
		}
		
		$items = $conn->fetchAll('SELECT `email` FROM `'.CoreTables::USER_TBL.'` WHERE '
			. '`email` LIKE :email AND `id` NOT IN(SELECT `userId` FROM `'.CoreTables::AREA_MEMBER_TBL.'` WHERE `areaId` = :area) ORDER BY `email` DESC LIMIT 15', [':area' => $this->getId(), ':email' => $mailQuery.'%']);
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
			. 'INNER JOIN `'.CoreTables::AREA_MEMBER_TBL.'` p ON p.`userId` = u.`id` WHERE p.`areaId` = :areaId ORDER BY p.role DESC, u.name');
		$stmt->bindValue(':areaId', $this->getId());
		$stmt->execute();
		$result = array();
		
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$role = $roleResolver->getRole('Area', $row['role']);
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
			Join::create(CoreTables::AREA_MEMBER_TBL, 'm', QueryClause::clause('m.`userId` = u.`id`')),
			QueryClause::clause('u.`id` = :id', ':id', $id)
		);
	}

	public function joinMember(Connection $conn, User $user, MembershipRole $role, $note)
	{
		$ifExists = $conn->fetchColumn('SELECT `userId` FROM `'.CoreTables::AREA_MEMBER_TBL.'` WHERE `areaId` = :area AND `userId` = :user', [':area' => $this->getId(), ':user' => $user->getId()]);
		if (false === $ifExists) {
			$conn->insert(CoreTables::AREA_MEMBER_TBL, ['areaId' => $this->getId(), 'userId' => $user->getId(), 'role' => $role->getId(), 'note' => $note]);
			$conn->executeQuery('UPDATE `'.CoreTables::USER_TBL.'` SET `areaNum` = (`areaNum` + 1) WHERE `id` = :id', [':id' => $user->getId()]);
			$conn->executeQuery('UPDATE `'.CoreTables::AREA_TBL.'` SET `memberNum` = (`memberNum` + 1) WHERE `id` = :id', [':id' => $this->getId()]);
			return true;
		}
		return false;
	}
	
	public function editMember(Connection $conn, User $user, MembershipRole $role, $note)
	{
		return 1 == $conn->update(CoreTables::AREA_MEMBER_TBL, ['role' => (int) $role->getId(), 'note' => $note], ['areaId' => $this->getId(), 'userId' => $user->getId()]);
	}

	public function removeMember(Connection $conn, User $user)
	{
		if (1 == $conn->delete(CoreTables::AREA_MEMBER_TBL, ['areaId' => $this->getId(), 'userId' => $user->getId()])) {
			$conn->executeQuery('UPDATE `'.CoreTables::USER_TBL.'` SET `areaNum` = (`areaNum` - 1) WHERE `id` = :id', [':id' => $user->getId()]);
			$conn->executeQuery('UPDATE `'.CoreTables::AREA_TBL.'` SET `memberNum` = (`memberNum` - 1) WHERE `id` = :id', [':id' => $this->getId()]);
			return true;
		}
		return false;
	}
}