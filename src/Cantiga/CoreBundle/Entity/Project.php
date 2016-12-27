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
use Cantiga\Components\Hierarchy\MembershipRoleResolverInterface;
use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;
use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointFilter;
use Cantiga\CoreBundle\Api\ModuleAwareInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Traits\PlaceTrait;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\Membership;
use Cantiga\UserBundle\UserTables;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;

class Project implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, HierarchicalInterface
{
	use PlaceTrait;
	
	private $id;
	private $name;
	private $slug;
	private $description;
	private $parentProject = null;
	private $modules = [];
	private $areasAllowed = false;
	private $areaRegistrationAllowed = false;
	private $archived = false;
	private $createdAt =  null;
	private $archivedAt = null;
	
	private $pendingArchivization = false;
	
	/**
	 * Fetches an array of ID-s of the active projects.
	 * 
	 * @param Connection $conn
	 * @return array
	 */
	public static function fetchActiveIds(Connection $conn)
	{
		$list = [];
		foreach ($conn->fetchAll('SELECT `id` FROM `'.CoreTables::PROJECT_TBL.'` WHERE `archived` = 0') as $row) {
			$list[] = $row['id'];
		}
		return $list;
	}

	public static function fetchActive(Connection $conn, $id)
	{
		$data = $conn->fetchAssoc('SELECT p.*, a.id AS `parent_id`, a.name AS `parent_name`, '
			. self::createPlaceFieldList()
			. 'FROM `'.CoreTables::PROJECT_TBL.'` p '
			. self::createPlaceJoin('p')
			. 'LEFT JOIN `'.CoreTables::PROJECT_TBL.'` a ON a.`id` = p.`parentProjectId` '
			. 'WHERE p.`id` = :id AND p.`archived` = 0', [':id' => $id]);
		if (false === $data) {
			return false;
		}
		$item = self::fromArray($data);
		$item->place = Place::fromArray($data, 'place');
		
		if (!empty($data['parent_id'])) {
			$item->parentProject = new ArchivedProjectRef($data['parent_id'], $data['parent_name']);
		}
		
		return $item;
	}
	
	public static function fetchBySlug(Connection $conn, $slug)
	{
		$data = $conn->fetchAssoc('SELECT p.*, a.id AS `parent_id`, a.name AS `parent_name`, '
			. self::createPlaceFieldList()
			. 'FROM `'.CoreTables::PROJECT_TBL.'` p '
			. self::createPlaceJoin('p')
			. 'LEFT JOIN `'.CoreTables::PROJECT_TBL.'` a ON a.`id` = p.`parentProjectId` '
			. 'WHERE p.`slug` = :slug AND p.`archived` = 0', [':slug' => $slug]);
		if (false === $data) {
			return false;
		}
		$item = self::fromArray($data);
		$item->place = Place::fromArray($data, 'place');
		
		if (!empty($data['parent_id'])) {
			$item->parentProject = new ArchivedProjectRef($data['parent_id'], $data['parent_name']);
		}
		
		return $item;
	}
	
	public static function fetch(Connection $conn, $id)
	{
		$data = $conn->fetchAssoc('SELECT p.*, a.id AS `parent_id`, a.name AS `parent_name`, '
			. self::createPlaceFieldList()
			. 'FROM `'.CoreTables::PROJECT_TBL.'` p '
			. self::createPlaceJoin('p')
			. 'LEFT JOIN `'.CoreTables::PROJECT_TBL.'` a ON a.`id` = p.`parentProjectId` '
			. 'WHERE p.`id` = :id', [':id' => $id]);
		if (false === $data) {
			return false;
		}
		$item = self::fromArray($data);
		$item->place = Place::fromArray($data, 'place');
		
		if (!empty($data['parent_id'])) {
			$item->parentProject = new ArchivedProjectRef($data['parent_id'], $data['parent_name']);
		}
		
		return $item;
	}

	/**
	 * Fetches the project by the place. The place can be given either as an object, or as an ID.
	 * 
	 * @param Connection $conn
	 * @param PlaceRef|int $place
	 * @return Project instance or FALSE
	 */
	public static function fetchByPlaceRef(Connection $conn, $place)
	{
		if (is_int($place)) {
			$placeId = (int) $place;
		} elseif ($place instanceof PlaceRef) {
			$placeId = $place->getId();
		} else {
			throw new InvalidArgumentException('The second argument of Project::fetchByPlaceRef() must be PlaceRef instance or integer.');
		}
	
		$data = $conn->fetchAssoc('SELECT p.*, a.id AS `parent_id`, a.name AS `parent_name`, '
			. self::createPlaceFieldList()
			. 'FROM `'.CoreTables::PROJECT_TBL.'` p '
			. self::createPlaceJoin('p')
			. 'LEFT JOIN `'.CoreTables::PROJECT_TBL.'` a ON a.`id` = p.`parentProjectId` '
			. 'WHERE p.`placeId` = :placeId', [':placeId' => $placeId]);
		if(false === $data) {
			return false;
		}
		$project = self::fromArray($data);
		$project->place = Place::fromArray($data, 'place');
		if (!empty($data['parent_id'])) {
			$project->parentProject = new ArchivedProjectRef($data['parent_id'], $data['parent_name']);
		}
		
		return $project;
	}
	
	public static function fetchForImport(Connection $conn, Project $currentProject, CantigaUserRefInterface $user)
	{	
		if (null === $currentProject->getParentProject()) {
			return false;
		}
		
		$data = $conn->fetchAssoc('SELECT p.*, '
			. self::createPlaceFieldList()
			. 'FROM `'.CoreTables::PROJECT_TBL.'` p '
			. self::createPlaceJoin('p')
			. 'INNER JOIN `'.UserTables::PLACE_MEMBERS_TBL.'` m ON m.`placeId` = e.`id` '
			. 'WHERE p.`id` = :parentProjectId AND m.`userId` = :userId', [':parentProjectId' => $currentProject->getParentProject()->getId(), ':userId' => $user->getId()]);
		if (false === $data) {
			return false;
		}
		$project = self::fromArray($data);
		$project->place = Place::fromArray($data, 'place');
		return $project;
	}

	/**
	 * @param Connection $conn
	 * @param int $projectId
	 * @param int $userId
	 * @return Membership
	 */
	public static function fetchMembership(Connection $conn, MembershipRoleResolverInterface $resolver, $slug, $userId)
	{
		$data = $conn->fetchAssoc('SELECT p.*, '
			. 'm.`role` AS `membership_role`, m.`note` AS `membership_note`, '
			. self::createPlaceFieldList()
			. 'FROM `'.CoreTables::PROJECT_TBL.'` p '
			. self::createPlaceJoin('p')
			. 'INNER JOIN `'.UserTables::PLACE_MEMBER_TBL.'` m ON m.`placeId` = e.`id` '
			. 'WHERE m.`userId` = :userId AND p.`slug` = :slug', [':userId' => $userId, ':slug' => $slug]);
		if(false === $data) {
			return false;
		}
		$project = self::fromArray($data);
		$project->place = Place::fromArray($data, 'place');
		$role = $resolver->getRole('Project', $data['membership_role']);
		return new Membership($project, $role, $data['membership_note']);
	}
	
	/**
	 * Finds the project of the given ID, where the area registration is currently possible. False is returned,
	 * if the project is not found.
	 * 
	 * @param Connection $conn
	 * @param int $projectId
	 * @return Project|boolean
	 */
	public static function fetchAvailableForRegistration(Connection $conn, $projectId)
	{
		$data = $conn->fetchAssoc('SELECT p.*, '
			. self::createPlaceFieldList()
			. 'FROM `'.CoreTables::PROJECT_TBL.'` p '
			. self::createPlaceJoin('p')
			. 'WHERE p.`id` = :id AND p.`archived` = 0 AND p.`areasAllowed` = 1 AND p.`areaRegistrationAllowed` = 1', [':id' => $projectId]);
		if (empty($data)) {
			return false;
		}
		$item = self::fromArray($data);
		$item->place = Place::fromArray($data, 'place');
		return $item;
	}

	public static function fromArray($array, $prefix = '')
	{
		$item = new Project;
		DataMappers::fromArray($item, $array, $prefix);
		if (is_string($item->modules)) {
			if ($item->modules === '') {
				$item->modules = [];
			} else {
				$item->modules = explode(',', $item->modules);
			}
		}
		return $item;
	}
	
	public static function getRelationships()
	{
		return ['parentProject'];
	}
	
	public function getTypeName():string
	{
		return 'Project';
	}
	
	public function isRoot(): bool
	{
		return true;
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

	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @return ArchivedProjectRef
	 */
	public function getParentProject()
	{
		return $this->parentProject;
	}

	public function getModules()
	{
		return $this->modules;
	}

	public function getAreasAllowed()
	{
		return $this->areasAllowed;
	}
	
	public function getAreaRegistrationAllowed()
	{
		return $this->areaRegistrationAllowed;
	}

	public function getArchived()
	{
		return $this->archived;
	}

	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	public function getArchivedAt()
	{
		return $this->archivedAt;
	}

	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	public function setParentProject($parentProject)
	{
		$this->parentProject = $parentProject;
		return $this;
	}

	public function setModules($modules)
	{
		$this->modules = $modules;
		return $this;
	}

	public function setAreasAllowed($areasAllowed)
	{
		$this->areasAllowed = $areasAllowed;
		return $this;
	}

	public function setAreaRegistrationAllowed($areaRegistrationAllowed)
	{
		$this->areaRegistrationAllowed = $areaRegistrationAllowed;
		return $this;
	}
	
	public function setArchived($archived)
	{
		$this->archived = $archived;
		return $this;
	}

	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;
		return $this;
	}

	public function setArchivedAt($archivedAt)
	{
		$this->archivedAt = $archivedAt;
		return $this;
	}
	
	/**
	 * Checks whether the given module-aware item is supported, according to the module
	 * configuration of this project.
	 * 
	 * @param ModuleAwareInterface $moduleAware Item to check
	 * @return boolean
	 */
	public function supports(ModuleAwareInterface $moduleAware)
	{
		return $this->supportsModule($moduleAware->getModule());
	}
	
	/**
	 * Checks whether this project supports the given module.
	 * 
	 * @param string $moduleName
	 */
	public function supportsModule($moduleName): bool
	{
		if ($moduleName == 'core') {
			return true;
		}
		return in_array($moduleName, $this->modules);
	}
	
	/**
	 * Builds an extension point filter that limits the available implementations to those
	 * ones which belong to the modules activated for this project.
	 */
	public function createExtensionPointFilter(): ExtensionPointFilter
	{
		$modules = $this->modules;
		$modules[] = 'core';
		return new ExtensionPointFilter($modules);
	}
	
	/**
	 * Sends the project to archive. Additional actions from other modules should be performed by attaching
	 * to <tt>CantigaEvents::PROJECT_ARCHIVIZED</tt> event. To check whether the archivization has been called
	 * on the entity, call {@link #isPendingArchivization()}.
	 */
	public function archivize()
	{
		if (!$this->archived) {
			$this->archivedAt = time();
			$this->archived = true;
			$this->pendingArchivization = true;
			$this->place->archivize();
		}
	}
	
	/**
	 * Informs that the archivization operation has been performed on this project.
	 */
	public function isPendingArchivization(): bool
	{
		return $this->pendingArchivization;
	}
	
	public function insert(Connection $conn)
	{
		$this->slug = DataMappers::generateSlug($conn, CoreTables::PROJECT_TBL);
		
		$this->place = new Place();
		$this->place->setType('Project');
		$this->place->setName($this->name);
		$this->place->setSlug($this->slug);
		$this->place->setRootPlaceId(NULL);
		$this->place->insert($conn);
		
		$conn->insert(
			CoreTables::PROJECT_TBL,
			DataMappers::pick($this, ['name', 'slug', 'description', 'parentProject', 'areasAllowed', 'areaRegistrationAllowed', 'place'], [
				'modules' => implode(',', $this->getModules()),
				'archived' => false,
				'createdAt' => time(),
				'archivedAt' => null,
			])
		);
		return $this->id = $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		$this->place->setName($this->name);
		$this->place->update($conn);
		
		return $conn->update(
			CoreTables::PROJECT_TBL,
			DataMappers::pick($this, ['name', 'description', 'parentProject', 'areasAllowed', 'areaRegistrationAllowed', 'archived', 'archivedAt'], [
				'modules' => implode(',', $this->getModules())
			]),
			DataMappers::pick($this, ['id'])
		);
	}

	public function getElementOfType(int $type)
	{
		if ($type == HierarchicalInterface::TYPE_PROJECT) {
			return $this;
		}
		return null;
	}

	public function getParents(): array
	{
		return [];
	}

	public function getRootElement(): HierarchicalInterface
	{
		return $this;
	}
	
	public function isChild(HierarchicalInterface $place): bool
	{
		if ($place->getRootElement()->getId() == $this->getId()) {
			return true;
		}
		return false;
	}
}