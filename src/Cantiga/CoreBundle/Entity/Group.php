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

use Cantiga\Components\Hierarchy\Entity\PlaceRef;
use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Traits\PlaceTrait;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\UserBundle\UserTables;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Group implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface, HierarchicalInterface
{
	use PlaceTrait;
	
	private $id;
	private $name;
	private $slug;
	private $project;
	private $category;
	private $notes;
	private $areaNum;
	
	public static function fetchByProject(Connection $conn, $id, Project $project)
	{
		$data = $conn->fetchAssoc('SELECT g.*, '
			. self::createPlaceFieldList()
			. 'FROM `'.CoreTables::GROUP_TBL.'` g '
			. self::createPlaceJoin('g')
			. 'WHERE g.`id` = :id AND g.`projectId` = :projectId', [':id' => $id, ':projectId' => $project->getId()]);
		if(null === $data) {
			return false;
		}
		$item = Group::fromArray($data);
		$item->project = $project;
		
		if (!empty($data['categoryId'])) {
			$item->category = GroupCategory::fetchByProject($conn, $data['categoryId'], $project);
		}
		$item->place = Place::fromArray($data, 'place');
		
		return $item;
	}
	
	public static function fetch(Connection $conn, $id)
	{
		$data = $conn->fetchAssoc('SELECT g.*, '
			. self::createPlaceFieldList()
			. 'FROM `'.CoreTables::GROUP_TBL.'` g '
			. self::createPlaceJoin('g')
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
		$item->place = Place::fromArray($data, 'place');
		
		return $item;
	}
	
	public static function fetchForImport(Connection $conn, Group $currentGroup, CantigaUserRefInterface $user)
	{
		if (null === $currentGroup->getProject()->getParentProject()) {
			return false;
		}
		
		$data = $conn->fetchAssoc('SELECT g.*, '
			. self::createPlaceFieldList()
			. 'FROM `'.CoreTables::GROUP_TBL.'` g '
			. self::createPlaceJoin('g')
			. 'INNER JOIN `'.UserTables::PLACE_MEMBERS_TBL.'` m ON m.`placeId` = e.`id` '
			. 'INNER JOIN `'.CoreTables::PROJECT_TBL.'` p ON p.`id` = g.`projectId` '
			. 'WHERE g.`name` = :name AND g.`projectId` = :parentProject AND m.`userId` = :userId', [
				':name' => $currentGroup->getName(), ':parentProject' => $currentGroup->getProject()->getParentProject()->getId(), ':userId' => $user->getId()]);
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
		$item->place = Place::fromArray($data, 'place');
		
		return $item;
	}
	
	public static function fetchByPlaceRef(Connection $conn, PlaceRef $place)
	{
		$data = $conn->fetchAssoc('SELECT g.*, '
			. self::createPlaceFieldList()
			. 'FROM `'.CoreTables::GROUP_TBL.'` g '
			. self::createPlaceJoin('g')
			. 'WHERE g.`placeId` = :placeId', [':placeId' => $place->getId()]);
		if(false === $data) {
			return false;
		}
		$group = self::fromArray($data);
		$group->project = Project::fetchActive($conn, $data['projectId']);
		$group->place = Place::fromArray($data, 'place');
		
		if (!empty($data['categoryId'])) {
			$group->category = GroupCategory::fetchByProject($conn, $data['categoryId'], $group->project);
		}
		
		return $group;
	}

	public static function fromArray($array, $prefix = '')
	{
		$item = new Group;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public static function getRelationships()
	{
		return ['project', 'category', 'place'];
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
	
	public function getAreaNum()
	{
		return $this->areaNum;
	}

	public function setAreaNum($areaNum)
	{
		$this->areaNum = $areaNum;
		return $this;
	}
	
	public function insert(Connection $conn)
	{
		$this->slug = DataMappers::generateSlug($conn, CoreTables::GROUP_TBL);
		
		$this->place = new Place();
		$this->place->setType('Group');
		$this->place->setName($this->name);
		$this->place->setSlug($this->slug);
		$this->place->setRootPlaceId($this->project->getPlace()->getId());
		$this->place->insert($conn);
		
		$conn->insert(
			CoreTables::GROUP_TBL,
			DataMappers::pick($this, ['name', 'slug', 'project', 'category', 'notes', 'place'])
		);
		return $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		$conn->executeQuery('UPDATE `'.CoreTables::AREA_TBL.'` SET `groupName` = :groupName WHERE `groupId` = :id', [
			':groupName' => $this->name,
			':id' => $this->id
		]);
		$this->place->setName($this->name);
		$this->place->update($conn);
		
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
			$this->place->remove($conn);
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
	
	public function isChild(HierarchicalInterface $place): bool
	{
		if ($place instanceof Area) {
			$group = $place->getGroup();
			if (null !== $group && $group->getId() == $this->getId()) {
				return true;
			}
		}
		return false;
	}
}