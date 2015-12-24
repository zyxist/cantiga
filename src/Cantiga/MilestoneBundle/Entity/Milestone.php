<?php
namespace Cantiga\MilestoneBundle\Entity;

use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\MilestoneBundle\MilestoneTables;
use Doctrine\DBAL\Connection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Milestone implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	const STATUS_NEW = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_CLOSED = 2;
	
	private $id;
	private $project;
	private $name;
	private $description;
	private $displayOrder;
	private $status;
	private $entityType;
	private $deadline;
	
	public static function fetchByProject(Connection $conn, $id, Project $project)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.MilestoneTables::MILESTONE_TBL.'` WHERE `id` = :id AND `projectId` = :projectId', [':id' => $id, ':projectId' => $project->getId()]);
		if (empty($data)) {
			return false;
		}
		$item = self::fromArray($data);
		$item->project = $project;
		return $item;
	}
	
	public static function fromArray($array, $prefix = '')
	{
		$item = new Milestone;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public static function getRelationships()
	{
		return ['project'];
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata) {
		$metadata->addPropertyConstraint('name', new NotBlank());
		$metadata->addPropertyConstraint('name', new Length(['min' => 2, 'max' => 60]));
		$metadata->addPropertyConstraint('description', new NotBlank());
		$metadata->addPropertyConstraint('description', new Length(['min' => 2, 'max' => 400]));
		$metadata->addPropertyConstraint('displayOrder', new Range(['min' => 0, 'max' => 10000]));
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
	
	public function getProject()
	{
		return $this->project;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getDisplayOrder()
	{
		return $this->displayOrder;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getEntityType()
	{
		return $this->entityType;
	}

	public function getDeadline()
	{
		return $this->deadline;
	}

	public function setProject(Project $project)
	{
		$this->project = $project;
		return $this;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	public function setDisplayOrder($displayOrder)
	{
		$this->displayOrder = $displayOrder;
		return $this;
	}

	public function setStatus($status)
	{
		DataMappers::noOverwritingField($this->status);
		$this->status = $status;
		return $this;
	}

	public function setEntityType($entityType)
	{
		$this->entityType = $entityType;
		return $this;
	}

	public function setDeadline($deadline)
	{
		$this->deadline = $deadline;
		return $this;
	}
	
	public static function statusText($status)
	{
		switch($status) {
			case self::STATUS_NEW:
				return 'New';
			case self::STATUS_ACTIVE:
				return 'Active';
			case self::STATUS_CLOSED:
				return 'Closed';
		}
	}
	
	public function getStatusText()
	{
		return self::statusText($this->status);
	}

	public function insert(Connection $conn)
	{
		$this->status = self::STATUS_NEW;
		$conn->insert(
			MilestoneTables::MILESTONE_TBL,
			DataMappers::pick($this, ['name', 'description', 'project', 'displayOrder', 'status', 'entityType', 'deadline'])
		);
		return $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		return $conn->update(
			MilestoneTables::MILESTONE_TBL,
			DataMappers::pick($this, ['name', 'description', 'displayOrder', 'status', 'deadline']),
			DataMappers::pick($this, ['id'])
		);
	}
	
	public function canRemove()
	{
		return $this->status == self::STATUS_NEW;
	}
	
	public function remove(Connection $conn)
	{
		$conn->delete(MilestoneTables::MILESTONE_TBL, DataMappers::pick($this, ['id']));
	}
}