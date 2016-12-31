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
use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Traits\PlaceTrait;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\UserBundle\UserTables;
use Doctrine\DBAL\Connection;
use LogicException;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Area implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, HierarchicalInterface
{
	use PlaceTrait;
	
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
	private $createdAt;
	private $lastUpdatedAt;
	private $percentCompleteness;
	
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
		$item->setSlug('');
		return $item;
	}

	public static function fetchActive(Connection $conn, $id)
	{
		$data = $conn->fetchAssoc('SELECT a.*, '
			. 't.`id` AS `territory_id`, t.`name` AS `territory_name`, t.`areaNum` AS `territory_areaNum`, t.`requestNum` as `territory_requestNum`, '
			. self::createPlaceFieldList()
			. 'FROM `'.CoreTables::AREA_TBL.'` a '
			. 'INNER JOIN `'.CoreTables::TERRITORY_TBL.'` t ON t.`id` = a.`territoryId` '
			. self::createPlaceJoin('a')
			. 'INNER JOIN `'.CoreTables::PROJECT_TBL.'` p ON p.`id` = a.`projectId` WHERE a.`id` = :id', [':id' => $id]);
		if(null === $data) {
			return false;
		}
		$item = Area::fromArray($data);
		$item->project = Project::fetch($conn, $data['projectId']);
		if (false === $item->project) {
			return false;
		}
		
		$item->status = $item->oldStatus = AreaStatus::fetchByProject($conn, $data['statusId'], $item->project);
		$item->setTerritory($item->oldTerritory = Territory::fromArray($data, 'territory'));
		if (!empty($data['groupId'])) {
			$item->group = $item->oldGroup = Group::fetchByProject($conn, $data['groupId'], $item->project);
		}
		$item->place = Place::fromArray($data, 'place');
		return $item;
	}
	
	public static function fetchForImport(Connection $conn, Area $currentArea, CantigaUserRefInterface $user)
	{
		if (null === $currentArea->getProject()->getParentProject()) {
			return false;
		}
		
		$data = $conn->fetchAssoc('SELECT a.*, '
			. 't.`id` AS `territory_id`, t.`name` AS `territory_name`, t.`areaNum` AS `territory_areaNum`, t.`requestNum` as `territory_requestNum`, '
			. self::createPlaceFieldList()
			. 'FROM `'.CoreTables::AREA_TBL.'` a '
			. 'INNER JOIN `'.CoreTables::TERRITORY_TBL.'` t ON t.`id` = a.`territoryId` '
			. self::createPlaceJoin('a')
			. 'INNER JOIN `'.UserTables::PLACE_MEMBERS_TBL.'` m ON m.`placeId` = e.`id` '
			. 'INNER JOIN `'.CoreTables::PROJECT_TBL.'` p ON p.`id` = a.`projectId` '
			. 'WHERE a.`name` = :name AND a.`projectId` = :parentProject AND m.`userId` = :userId', [
				':name' => $currentArea->getName(), ':parentProject' => $currentArea->getProject()->getParentProject()->getId(), ':userId' => $user->getId()]);
		if(null === $data) {
			return false;
		}
		$item = Area::fromArray($data);
		$item->project = Project::fetch($conn, $data['projectId']);
		if (false === $item->project) {
			return false;
		}
		
		$item->status = $item->oldStatus = AreaStatus::fetchByProject($conn, $data['statusId'], $item->project);
		$item->setTerritory($item->oldTerritory = Territory::fromArray($data, 'territory'));
		if (!empty($data['groupId'])) {
			$item->group = $item->oldGroup = Group::fetchByProject($conn, $data['groupId'], $item->project);
		}
		$item->place = Place::fromArray($data, 'place');
		return $item;
	}
	
	/**
	 * @param Connection $conn
	 * @param int $id
	 * @param HierarchicalInterface $place Parent place
	 * @return Area
	 */
	public static function fetchByPlace(Connection $conn, $id, HierarchicalInterface $place)
	{
		if ($place instanceof Project) {
			$selector = 'a.`projectId` = :placeId';
		} elseif ($place instanceof Group) {
			$selector = 'a.`groupId` = :placeId';
		} else {
			throw new LogicException('The specified place type is not supported.');
		}
		$data = $conn->fetchAssoc('SELECT a.*, '
			. 't.`id` AS `territory_id`, t.`name` AS `territory_name`, t.`areaNum` AS `territory_areaNum`, t.`requestNum` as `territory_requestNum`, '
			. self::createPlaceFieldList()
			. 'FROM `'.CoreTables::AREA_TBL.'` a '
			. self::createPlaceJoin('a')
			. 'INNER JOIN `'.CoreTables::TERRITORY_TBL.'` t ON t.`id` = a.`territoryId` '
			. 'WHERE a.`id` = :id AND '.$selector, [':id' => $id, ':placeId' => $place->getId()]);
		if(false === $data) {
			return false;
		}
		$item = Area::fromArray($data);
		if ($place->isRoot()) {
			$item->project = $place;
			if (!empty($data['groupId'])) {
				$item->group = $item->oldGroup = Group::fetchByProject($conn, $data['groupId'], $item->project);
			}
		} else {
			$item->group = $item->oldGroup = $place;
			$item->project = $place->getRootElement();
		}
		$item->status = $item->oldStatus = AreaStatus::fetchByProject($conn, $data['statusId'], $item->project);
		$item->setTerritory($item->oldTerritory = Territory::fromArray($data, 'territory'));
		$item->place = Place::fromArray($data, 'place');
		return $item;
	}
	
	public static function fetchByPlaceRef(Connection $conn, PlaceRef $place)
	{
		$data = $conn->fetchAssoc('SELECT a.*, '
			. 't.`id` AS `territory_id`, t.`name` AS `territory_name`, t.`areaNum` AS `territory_areaNum`, t.`requestNum` as `territory_requestNum`, '
			. self::createPlaceFieldList()
			. 'FROM `'.CoreTables::AREA_TBL.'` a '
			. self::createPlaceJoin('a')
			. 'INNER JOIN `'.CoreTables::TERRITORY_TBL.'` t ON t.`id` = a.`territoryId` '
			. 'WHERE a.`placeId` = :placeId', [':placeId' => $place->getId()]);
		if(false === $data) {
			return false;
		}
		$area = self::fromArray($data);
		$area->project = Project::fetch($conn, $data['projectId']);
		$area->place = Place::fromArray($data, 'place');
		$area->status = $area->oldStatus = AreaStatus::fetchByProject($conn, $data['statusId'], $area->project);
		$area->setTerritory($area->oldTerritory = Territory::fromArray($data, 'territory'));
		
		if (!empty($data['groupId'])) {
			$area->group = $area->oldGroup = Group::fetchByProject($conn, $data['groupId'], $area->project);
		}
		
		return $area;
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
		return ['project', 'status', 'group', 'territory', 'reporter', 'place'];
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata) {
		$metadata->addPropertyConstraint('name', new NotBlank());
		$metadata->addPropertyConstraint('name', new Length(array('min' => 2, 'max' => 100)));
	}
	
	public function getTypeName():string
	{
		return 'Area';
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
	
	/**
	 * @return Project
	 */
	public function getProject()
	{
		return $this->project;
	}
	
	public function setProject(Project $project)
	{
		$this->project = $project;
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

	public function setGroup(Group $group = null)
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
	
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	public function getLastUpdatedAt()
	{
		return $this->lastUpdatedAt;
	}

	public function setCreatedAt($createdAt)
	{
		DataMappers::noOverwritingField($this->createdAt);
		$this->createdAt = $createdAt;
		return $this;
	}

	public function setLastUpdatedAt($lastUpdatedAt)
	{
		DataMappers::noOverwritingField($this->lastUpdatedAt);
		$this->lastUpdatedAt = $lastUpdatedAt;
		return $this;
	}
	
	public function getPercentCompleteness()
	{
		return $this->percentCompleteness;
	}

	public function setPercentCompleteness($percentCompleteness)
	{
		if ($percentCompleteness > 100) {
			$percentCompleteness = 100;
		} elseif($percentCompleteness < 0) {
			$percentCompleteness = 0;
		}
		$this->percentCompleteness = $percentCompleteness;
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
		$this->slug = DataMappers::generateSlug($conn, CoreTables::AREA_TBL);
		
		$this->place = new Place();
		$this->place->setType('Area');
		$this->place->setName($this->name);
		$this->place->setSlug($this->slug);
		$this->place->setRootPlaceId($this->project->getPlace()->getId());
		$this->place->insert($conn);
		
		$this->createdAt = $this->lastUpdatedAt = time();
		if (empty($this->percentCompleteness)) {
			$this->percentCompleteness = 0;
		}
		$conn->insert(
			CoreTables::AREA_TBL,
			DataMappers::pick($this, ['name', 'slug', 'project', 'group', 'territory', 'status', 'reporter', 'place', 'createdAt', 'lastUpdatedAt', 'percentCompleteness'], ['customData' => json_encode($this->customData), 'groupName' => $groupName])
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
		
		$this->place->setName($this->name);
		$this->place->update($conn);
		$this->lastUpdatedAt = time();
		
		return $conn->update(
			CoreTables::AREA_TBL,
			DataMappers::pick($this, ['name', 'group', 'territory', 'status', 'lastUpdatedAt', 'percentCompleteness'], ['customData' => json_encode($this->customData), 'groupName' => $groupName]),
			DataMappers::pick($this, ['id'])
		);
	}

	public function getElementOfType(int $type)
	{
		if ($type == HierarchicalInterface::TYPE_PROJECT) {
			return $this->getProject();
		} elseif ($type == HierarchicalInterface::TYPE_GROUP) {
			return $this->getGroup();
		}
		return $this;
	}

	public function getParents(): array
	{
		if (null !== $this->group) {
			return [$this->project, $this->group];
		}
		return [$this->project];
	}

	public function getRootElement(): HierarchicalInterface
	{
		return $this->project;
	}
	
	public function isChild(HierarchicalInterface $place): bool
	{
		return false;
	}
}