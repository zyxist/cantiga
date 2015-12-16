<?php
namespace Cantiga\CoreBundle\Entity;

use Doctrine\DBAL\Connection;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;

class AreaStatus implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	private $id;
	private $name;
	private $label;
	private $isDefault;
	private $areaNum;
	private $project;
	
	public static function fetchByProject(Connection $conn, $id, Project $project)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.CoreTables::AREA_STATUS_TBL.'` WHERE `id` = :id AND `projectId` = :projectId', [':id' => $id, ':projectId' => $project->getId()]);
		if (empty($data)) {
			return false;
		}
		$item = self::fromArray($data);
		$item->project = $project;
		return $item;
	}
	
	public static function fetchDefault(Connection $conn, Project $project)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.CoreTables::AREA_STATUS_TBL.'` WHERE `isDefault` = 1 AND `projectId` = :projectId', [':projectId' => $project->getId()]);
		if (empty($data)) {
			return false;
		}
		$item = self::fromArray($data);
		$item->project = $project;
		return $item;
	}

	public static function fromArray($array, $prefix = '')
	{
		$item = new AreaStatus;
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
	
	public function getLabel()
	{
		return $this->label;
	}

	public function getIsDefault()
	{
		return $this->isDefault;
	}

	public function getAreaNum()
	{
		return $this->areaNum;
	}

	public function setLabel($label)
	{
		$this->label = $label;
		return $this;
	}

	public function setIsDefault($default)
	{
		$this->isDefault = $default;
		return $this;
	}

	public function setAreaNum($areaNum)
	{
		$this->areaNum = $areaNum;
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
		if ($this->isDefault) {
			$conn->executeUpdate('UPDATE `'.CoreTables::AREA_STATUS_TBL.'` SET `isDefault` = 0 AND `projectId` = :projectId', [':projectId' => $this->project->getId()]);
		}
		$conn->insert(
			CoreTables::AREA_STATUS_TBL,
			DataMappers::pick($this, ['name', 'label', 'isDefault', 'project'])
		);
		return $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		if ($this->isDefault) {
			$conn->executeUpdate('UPDATE `'.CoreTables::AREA_STATUS_TBL.'` SET `isDefault` = 0 WHERE `id` <> :id AND `projectId` = :projectId', [':id' => $this->id, ':projectId' => $this->project->getId()]);
		}
		return $conn->update(
			CoreTables::AREA_STATUS_TBL,
			DataMappers::pick($this, ['name', 'label', 'isDefault', 'project']),
			DataMappers::pick($this, ['id'])
		);
	}
	
	public function canRemove()
	{
		return (!$this->isDefault);
	}
	
	public function remove(Connection $conn)
	{
		$conn->delete(CoreTables::AREA_STATUS_TBL, DataMappers::pick($this, ['id']));
	}
}