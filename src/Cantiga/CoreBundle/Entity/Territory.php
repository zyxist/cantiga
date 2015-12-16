<?php
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
			DataMappers::pick($this, ['name', 'project'])
		);
		return $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		return $conn->update(
			CoreTables::TERRITORY_TBL,
			DataMappers::pick($this, ['name', 'project']),
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