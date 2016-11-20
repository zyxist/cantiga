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
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Doctrine\DBAL\Connection;

class Territory implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	private $id;
	private $name;
	private $locale;
	private $areaNum;
	private $requestNum;
	private $project;
	
	public static function fetchByProject(Connection $conn, $id, Project $project)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.CoreTables::TERRITORY_TBL.'` WHERE `id` = :id AND `projectId` = :projectId', [':id' => $id, ':projectId' => $project->getId()]);
		if (empty($data)) {
			return false;
		}
		$item = self::fromArray($data);
		$item->project = $project;
		return $item;
	}

	public static function fromArray($array, $prefix = '')
	{
		$item = new Territory;
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

	public function getLocale()
	{
		return $this->locale;
	}

	public function setLocale($locale)
	{
		$this->locale = $locale;
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
	
	public function getRequestNum()
	{
		return $this->requestNum;
	}

	public function setRequestNum($areaNum)
	{
		$this->requestNum = $areaNum;
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

	public function insert(Connection $conn)
	{
		$conn->insert(
			CoreTables::TERRITORY_TBL,
			DataMappers::pick($this, ['name', 'locale', 'project'])
		);
		return $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		return $conn->update(
			CoreTables::TERRITORY_TBL,
			DataMappers::pick($this, ['name', 'locale', 'project']),
			DataMappers::pick($this, ['id'])
		);
	}
	
	public function canRemove()
	{
		return ($this->areaNum == 0 && $this->requestNum == 0);
	}
	
	public function remove(Connection $conn)
	{
		$this->areaNum = $conn->fetchColumn('SELECT `areaNum` FROM `'.CoreTables::TERRITORY_TBL.'` WHERE `id` = :id', [':id' => $this->id]);
		if ($this->canRemove()) {
			$conn->delete(CoreTables::TERRITORY_TBL, DataMappers::pick($this, ['id']));
			return true;
		}
		return false;
	}
}