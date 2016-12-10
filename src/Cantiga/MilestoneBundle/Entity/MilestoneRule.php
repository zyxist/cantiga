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

use Cantiga\CoreBundle\Entity\Place;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\MilestoneBundle\MilestoneTables;
use Doctrine\DBAL\Connection;
use LogicException;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Allows automatic completion of milestones, when certain events in the
 * system occur.
 *
 * @author Tomasz Jędrzejewski
 */
class MilestoneRule implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{

	private $id;
	/**
	 * @var Project
	 */
	private $project;
	/**
	 * @var Milestone
	 */
	private $milestone;
	private $name;
	private $activator;
	
	public static function fetchByProject(Connection $conn, $id, Project $project)
	{
		$data = $conn->fetchAssoc('SELECT r.*, m.`id` AS `milestone_id`, m.`name` AS `milestone_name`, m.`description` AS `milestone_description`, m.`displayOrder` AS `milestone_displayOrder`, '
			. 'm.`type` AS `milestone_type`, m.`entityType` AS `milestone_entityType`, m.`deadline` AS `milestone_deadline` '
			. 'FROM `'.MilestoneTables::MILESTONE_RULE_TBL.'` r '
			. 'INNER JOIN `'.MilestoneTables::MILESTONE_TBL.'` m ON m.`id` = r.`milestoneId` '
			. 'WHERE r.`id` = :id AND r.`projectId` = :projectId AND m.`entityType` = :entityType', [':id' => $id, ':projectId' => $project->getId()]);
		if (empty($data)) {
			return false;
		}
		$item = self::fromArray($data);
		$item->project = $project;
		$item->milestone = Milestone::fromArray($data, 'milestone');
		$item->milestone->setProject($project);
		return $item;
	}
	
	public static function fetchByActivator(Connection $conn, Project $project, $activator, Place $entity)
	{
		$data = $conn->fetchAssoc('SELECT r.*, m.`id` AS `milestone_id`, m.`name` AS `milestone_name`, m.`description` AS `milestone_description`, m.`displayOrder` AS `milestone_displayOrder`, '
			. 'm.`type` AS `milestone_type`, m.`entityType` AS `milestone_entityType`, m.`deadline` AS `milestone_deadline` '
			. 'FROM `'.MilestoneTables::MILESTONE_RULE_TBL.'` r '
			. 'INNER JOIN `'.MilestoneTables::MILESTONE_TBL.'` m ON m.`id` = r.`milestoneId` '
			. 'WHERE r.`activator` = :activator AND r.`projectId` = :projectId AND m.`entityType` = :entityType', [':activator' => $activator, ':projectId' => $project->getId(), ':entityType' => $entity->getType()]);
		if (empty($data)) {
			return false;
		}
		$item = self::fromArray($data);
		$item->project = $project;
		$item->milestone = Milestone::fromArray($data, 'milestone');
		$item->milestone->setProject($project);
		return $item;
	}

	public static function fromArray($array, $prefix = '')
	{
		$item = new MilestoneRule;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}

	public static function getRelationships()
	{
		return ['project', 'milestone'];
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

	public function getMilestone()
	{
		return $this->milestone;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getActivator()
	{
		return $this->activator;
	}

	public function setProject($project)
	{
		$this->project = $project;
		return $this;
	}

	public function setMilestone($milestone)
	{
		$this->milestone = $milestone;
		return $this;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function setActivator($activator)
	{
		$this->activator = $activator;
		return $this;
	}

	public function insert(Connection $conn)
	{
		$conn->insert(
			MilestoneTables::MILESTONE_RULE_TBL,
			DataMappers::pick($this, ['name', 'activator', 'project', 'milestone'])
		);
		return $this->id = $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		return $conn->update(
			MilestoneTables::MILESTONE_RULE_TBL,
			DataMappers::pick($this, ['name', 'activator', 'milestone']),
			DataMappers::pick($this, ['id'])
		);
	}

	public function canRemove()
	{
		return true;
	}

	public function remove(Connection $conn)
	{
		$conn->delete(MilestoneTables::MILESTONE_RULE_TBL, DataMappers::pick($this, ['id']));
	}
	
	/**
	 * Fires the rule against the given entity, using the callback that produces the new status
	 * for the milestone.
	 * 
	 * @param Connection $conn
	 * @param Place $entity
	 * @param callback $callback
	 */
	public function fireRule(Connection $conn, Place $entity, $callback)
	{
		if ($this->milestone->isBeforeDeadline()) {
			$result = $callback();
			
			if (! ($result instanceof NewMilestoneStatus)) {
				throw new LogicException('Cannot fire the milestone rule \''.$this->activator+'\': the callback did not return a NewMilestoneStatus instance!');
			}
			
			if ($result->isChanged()) {
				if ($this->milestone->getType() == Milestone::TYPE_BINARY) {
					if ($result->isCompleted()) {
						$this->milestone->complete($conn, $entity);
					} else {
						$this->milestone->cancel($conn, $entity);
					}
				} else {
					$this->milestone->updateProgress($conn, $entity, $result->getProgress());
				}
			}
		}
	}
}
