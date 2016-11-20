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

namespace Cantiga\MilestoneBundle\Entity;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\AreaStatus;
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
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Allows automatic updates of the area status, when certain milestones are completed.
 *
 * @author Tomasz Jędrzejewski
 */
class MilestoneStatusRule implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	private $id;
	private $project;
	private $name;
	private $newStatus;
	private $prevStatus;
	private $milestoneMap;
	private $activationOrder;
	private $lastUpdatedAt;
	
	private $milestoneSummary = [];
	
	public static function fetchByProject(Connection $conn, $id, Project $project)
	{
		$data = $conn->fetchAssoc('SELECT r.*, '
			. ' s1.`id` AS `s1_id`, s1.`name` AS `s1_name`, s1.`label` AS `s1_label`, s1.`isDefault` AS `s1_isDefault`, s1.`areaNum` AS `s1_areaNum`, '
			. ' s2.`id` AS `s2_id`, s2.`name` AS `s2_name`, s2.`label` AS `s2_label`, s2.`isDefault` AS `s2_isDefault`, s2.`areaNum` AS `s2_areaNum` '
			. 'FROM `'.MilestoneTables::MILESTONE_STATUS_RULE_TBL.'` r '
			. 'INNER JOIN `'.CoreTables::AREA_STATUS_TBL.'` s1 ON r.`newStatusId` = s1.`id` '
			. 'INNER JOIN `'.CoreTables::AREA_STATUS_TBL.'` s2 ON r.`prevStatusId` = s2.`id` '
			. 'WHERE r.`id` = :id AND r.`projectId` = :projectId', [':id' => $id, ':projectId' => $project->getId()]);
		if (empty($data)) {
			return false;
		}
		$item = self::fromArray($data);
		$item->project = $project;
		$item->newStatus = AreaStatus::fromArray($data, 's1');
		$item->prevStatus = AreaStatus::fromArray($data, 's2');
		$item->newStatus->setProject($project);
		$item->prevStatus->setProject($project);
		
		return $item;
	}
	
	public static function fromArray($array, $prefix = '')
	{
		$item = new MilestoneStatusRule;
		DataMappers::fromArray($item, $array, $prefix);
		if (is_string($item->milestoneMap)) {
			$item->milestoneMap = explode(',', $item->milestoneMap);
		}
		return $item;
	}

	public static function getRelationships()
	{
		return ['project', 'prevStatus', 'newStatus'];
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('name', new NotBlank());
		$metadata->addPropertyConstraint('name', new Length(['min' => 2, 'max' => 80]));
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getProject()
	{
		return $this->project;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getNewStatus()
	{
		return $this->newStatus;
	}

	public function getPrevStatus()
	{
		return $this->prevStatus;
	}

	public function getMilestoneMap()
	{
		return $this->milestoneMap;
	}

	public function getActivationOrder()
	{
		return $this->activationOrder;
	}

	public function getLastUpdatedAt()
	{
		return $this->lastUpdatedAt;
	}
	
	public function getMilestoneSummary()
	{
		return $this->milestoneSummary;
	}

	public function setId($id)
	{
		DataMappers::noOverwritingId($this->id);
		$this->id = $id;
		return $this;
	}
	
	public function setProject(Project $project)
	{
		DataMappers::noOverwritingField($this->project);
		$this->project = $project;
		return $this;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function setNewStatus($newStatus)
	{
		$this->newStatus = $newStatus;
		return $this;
	}

	public function setPrevStatus($prevStatus)
	{
		$this->prevStatus = $prevStatus;
		return $this;
	}

	public function setMilestoneMap($milestoneMap)
	{
		$this->milestoneMap = $milestoneMap;
		return $this;
	}

	public function setActivationOrder($activationOrder)
	{
		$this->activationOrder = $activationOrder;
		return $this;
	}

	public function setLastUpdatedAt($lastUpdatedAt)
	{
		$this->lastUpdatedAt = $lastUpdatedAt;
		return $this;
	}
	
	public function fetchMilestoneSummary(Connection $conn)
	{
		if (sizeof($this->milestoneMap) > 0) {
			$this->milestoneSummary = $conn->fetchAll('SELECT `id`, `name` FROM `'.MilestoneTables::MILESTONE_TBL.'` '
				. 'WHERE `id` IN ('.implode(',', $this->milestoneMap).')');
		}
	}

	public function insert(Connection $conn)
	{
		$this->lastUpdatedAt = time();
		$conn->insert(
			MilestoneTables::MILESTONE_STATUS_RULE_TBL,
			DataMappers::pick($this, ['name', 'project', 'newStatus', 'prevStatus', 'activationOrder', 'lastUpdatedAt'], [
				'milestoneMap' => implode(',', $this->getMilestoneMap())
			])
		);
		return $this->id = $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		$this->lastUpdatedAt = time();
		return $conn->update(
			MilestoneTables::MILESTONE_STATUS_RULE_TBL,
			DataMappers::pick($this, ['name', 'newStatus', 'prevStatus', 'activationOrder', 'lastUpdatedAt'], [
				'milestoneMap' => implode(',', $this->getMilestoneMap())
			]),
			DataMappers::pick($this, ['id'])
		);
	}
	
	public function canRemove()
	{
		return true;
	}

	public function remove(Connection $conn)
	{
		$conn->delete(MilestoneTables::MILESTONE_STATUS_RULE_TBL, DataMappers::pick($this, ['id']));
	}

}
