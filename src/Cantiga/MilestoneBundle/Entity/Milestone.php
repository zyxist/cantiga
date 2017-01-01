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
use Cantiga\CoreBundle\Entity\Place;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\MilestoneBundle\MilestoneTables;
use Doctrine\DBAL\Connection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Milestone implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	const TYPE_BINARY = 0;
	const TYPE_PERCENT = 1;
	
	const RETURN_SUMMARY = true;
	const RETURN_BOOLEAN = false;
	
	private $id;
	private $project;
	private $name;
	private $description;
	private $displayOrder;
	private $type;
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
	
	public static function fetchByProjectAndType(Connection $conn, $id, $type, Project $project)
	{	
		$data = $conn->fetchAssoc('SELECT * FROM `'.MilestoneTables::MILESTONE_TBL.'` WHERE `id` = :id AND `projectId` = :projectId AND `entityType` = :entityType', [':id' => $id, ':entityType' => $type, ':projectId' => $project->getId()]);
		if (false === $data) {
			return false;
		}
		$item = self::fromArray($data);
		$item->project = $project;
		return $item;
	}
	
	public static function populateEntities(Connection $conn, Place $newEntity, Project $project)
	{
		$conn->insert(MilestoneTables::MILESTONE_PROGRESS_TBL, ['entityId' => $newEntity->getId(), 'completedNum' => 0]);
		$milestones = $conn->fetchAll('SELECT `id` FROM `'.MilestoneTables::MILESTONE_TBL.'` WHERE `projectId` = :projectId AND `entityType` = :entityType', [':projectId' => $project->getId(), ':entityType' => $newEntity->getType()]);
		if (sizeof($milestones) > 0) {
			$stmt = $conn->prepare('INSERT INTO `'.MilestoneTables::MILESTONE_STATUS_TBL.'` (`entityId`, `milestoneId`, `progress`) VALUES(:entityId, :milestoneId, 0)');
			foreach ($milestones as $milestone) {
				$stmt->bindValue(':entityId', $newEntity->getId());
				$stmt->bindValue(':milestoneId', $milestone['id']);
				$stmt->execute();
			}
		}
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

	public function getType()
	{
		return $this->type;
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

	public function setType($type)
	{
		DataMappers::noOverwritingField($this->type);
		$this->type = $type;
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
	
	/**
	 * Checks whether the deadline has passed. If the deadline is not set, the method
	 * always returns <tt>true</tt>.
	 * 
	 * @return boolean
	 */
	public function isBeforeDeadline()
	{
		if (null !== $this->deadline) {
			return time() < ($this->deadline + 86400);
		}
		return true;
	}

	public static function typeText($status)
	{
		switch($status) {
			case self::TYPE_BINARY:
				return 'binary (yes-no)';
			case self::TYPE_PERCENT:
				return 'percent';
		}
	}
	
	public function getTypeText()
	{
		return self::typeText($this->type);
	}

	public function insert(Connection $conn)
	{
		$conn->insert(
			MilestoneTables::MILESTONE_TBL,
			DataMappers::pick($this, ['name', 'description', 'project', 'displayOrder', 'type', 'entityType', 'deadline'])
		);
		$this->id = $conn->lastInsertId();
		
		switch($this->entityType) {
			case 'Project':
				$stmt = $this->createStatusPopulator($conn);
				$stmt->bindValue(':entityId', $this->project->getPlace()->getId());
				$stmt->bindValue(':milestoneId', $this->id);
				$stmt->execute();
				break;
			case 'Group':
				$groupIds = $conn->fetchAll('SELECT `placeId` FROM `'.CoreTables::GROUP_TBL.'` WHERE `projectId` = :projectId', [':projectId' => $this->project->getId()]);
				$stmt = $this->createStatusPopulator($conn);
				foreach ($groupIds as $row) {
					$stmt->bindValue(':entityId', $row['placeId']);
					$stmt->bindValue(':milestoneId', $this->id);
					$stmt->execute();
				}
			case 'Area':
				$groupIds = $conn->fetchAll('SELECT `placeId` FROM `'.CoreTables::AREA_TBL.'` WHERE `projectId` = :projectId', [':projectId' => $this->project->getId()]);
				$stmt = $this->createStatusPopulator($conn);
				foreach ($groupIds as $row) {
					$stmt->bindValue(':entityId', $row['placeId']);
					$stmt->bindValue(':milestoneId', $this->id);
					$stmt->execute();
				}
		}
		
		return $this->id;
	}

	public function update(Connection $conn)
	{		
		return $conn->update(
			MilestoneTables::MILESTONE_TBL,
			DataMappers::pick($this, ['name', 'description', 'displayOrder', 'type', 'deadline']),
			DataMappers::pick($this, ['id'])
		);
	}
	
	public function canRemove()
	{
		return true;
	}
	
	public function remove(Connection $conn)
	{
		$conn->delete(MilestoneTables::MILESTONE_TBL, DataMappers::pick($this, ['id']));
	}
	
	/**
	 * Completes this milestone for the given entity. If <tt>$retVal</tt> is set to
	 * <tt>RETURN_BOOLEAN</tt>, the method returns just true or false to indicate
	 * the success or failure. If <tt>RETURN_SUMMARY</tt> is set, it returns an array that
	 * describes the new status of the milestone for this entity. In this case, you have
	 * to provide <tt>$timeFormatter</tt> instance, too.
	 * 
	 * @param Connection $conn
	 * @param Place $entity
	 * @param boolean $retVal Either Milestone::RETURN_BOOLEAN (default) or Milestone::RETURN_SUMMARY
	 * @param TimeFormatterInterface $timeFormatter Provide only if $retVal is set to Milestone::RETURN_SUMMARY
	 * @return mixed
	 * @throws ModelException
	 * @throws ItemNotFoundException
	 */
	public function complete(Connection $conn, Place $entity, $retVal = self::RETURN_BOOLEAN, TimeFormatterInterface $timeFormatter = null)
	{
		if ($this->getType() != Milestone::TYPE_BINARY) {
			throw new ModelException('Cannot change the state of the milestone!');
		}

		$currentProgress = $conn->fetchColumn('SELECT `progress` FROM `'.MilestoneTables::MILESTONE_STATUS_TBL.'` WHERE `entityId` = :entityId AND `milestoneId` = :milestoneId FOR UPDATE', [
			':entityId' => $entity->getId(),
			':milestoneId' => $this->getId()
		]);

		if (false === $currentProgress) {
			throw new ItemNotFoundException('Unknown milestone status.');
		}

		if ($currentProgress == 0) {
			$completedAt = time();
			$conn->update(MilestoneTables::MILESTONE_STATUS_TBL, ['progress' => 100, 'completedAt' => $completedAt], ['entityId' => $entity->getId(), 'milestoneId' => $this->getId()]);
			$conn->executeUpdate('UPDATE `'.MilestoneTables::MILESTONE_PROGRESS_TBL.'` SET `completedNum` = (`completedNum` + 1) WHERE `entityId` = :entityId', [':entityId' => $entity->getId()]);
			if ($retVal == self::RETURN_BOOLEAN) {
				return true;
			} else {
				return $this->createStatusRow(100, $completedAt, $timeFormatter);
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Sets a new progress of this milestone for the given entity. If <tt>$retVal</tt> is set to
	 * <tt>RETURN_BOOLEAN</tt>, the method returns just true or false to indicate
	 * the success or failure. If <tt>RETURN_SUMMARY</tt> is set, it returns an array that
	 * describes the new status of the milestone for this entity. In this case, you have
	 * to provide <tt>$timeFormatter</tt> instance, too.
	 * 
	 * @param Connection $conn
	 * @param Place $entity
	 * @param int $progress Progress as percentage (0-100)
	 * @param boolean $retVal Either Milestone::RETURN_BOOLEAN (default) or Milestone::RETURN_SUMMARY
	 * @param TimeFormatterInterface $timeFormatter Provide only if $retVal is set to Milestone::RETURN_SUMMARY
	 * @return mixed
	 * @throws ModelException
	 * @throws ItemNotFoundException
	 */
	public function updateProgress(Connection $conn, Place $entity, $progress, $retVal = self::RETURN_BOOLEAN, TimeFormatterInterface $timeFormatter = null)
	{
		if ($this->getType() != Milestone::TYPE_PERCENT) {
			throw new ModelException('Cannot change the state of the milestone!');
		}

		if ($progress < 0 || $progress > 100) {
			throw new ModelException('Invalid progress value!');
		}

		$change = $conn->fetchAssoc('SELECT `progress`, `completedAt` FROM `'.MilestoneTables::MILESTONE_STATUS_TBL.'` WHERE `entityId` = :entityId AND `milestoneId` = :milestoneId FOR UPDATE', [
			':entityId' => $entity->getId(),
			':milestoneId' => $this->getId()
		]);
		$currentProgress = $change['progress'];
		$change['progress'] = $progress;

		if ($currentProgress == 100 && $progress < 100) {
			$conn->executeUpdate('UPDATE `'.MilestoneTables::MILESTONE_PROGRESS_TBL.'` SET `completedNum` = (`completedNum` - 1) WHERE `entityId` = :entityId', [':entityId' => $entity->getId()]);
			$change['completedAt'] = null;
		} elseif ($currentProgress < 100 && $progress == 100) {
			$conn->executeUpdate('UPDATE `'.MilestoneTables::MILESTONE_PROGRESS_TBL.'` SET `completedNum` = (`completedNum` + 1) WHERE `entityId` = :entityId', [':entityId' => $entity->getId()]);
			$change['completedAt'] = time();
		}
		$conn->update(MilestoneTables::MILESTONE_STATUS_TBL, $change, ['entityId' => $entity->getId(), 'milestoneId' => $this->getId()]);
		if ($retVal == self::RETURN_BOOLEAN) {
			return true;
		} else {
			return $this->createStatusRow($change['progress'], $change['completedAt'], $timeFormatter);
		}
	}
	
	/**
	 * Cancels the completion of this milestone for the given entity. If <tt>$retVal</tt> is set to
	 * <tt>RETURN_BOOLEAN</tt>, the method returns just true or false to indicate
	 * the success or failure. If <tt>RETURN_SUMMARY</tt> is set, it returns an array that
	 * describes the new status of the milestone for this entity. In this case, you have
	 * to provide <tt>$timeFormatter</tt> instance, too.
	 * 
	 * @param Connection $conn
	 * @param Place $entity
	 * @param boolean $retVal Either Milestone::RETURN_BOOLEAN (default) or Milestone::RETURN_SUMMARY
	 * @param TimeFormatterInterface $timeFormatter Provide only if $retVal is set to Milestone::RETURN_SUMMARY
	 * @return mixed
	 * @throws ModelException
	 * @throws ItemNotFoundException
	 */
	public function cancel(Connection $conn, Place $entity, $retVal = self::RETURN_BOOLEAN, TimeFormatterInterface $timeFormatter = null)
	{
		if ($this->getType() != Milestone::TYPE_BINARY) {
			throw new ModelException('Cannot change the state of the milestone!');
		}

		$currentProgress = $conn->fetchColumn('SELECT `progress` FROM `'.MilestoneTables::MILESTONE_STATUS_TBL.'` WHERE `entityId` = :entityId AND `milestoneId` = :milestoneId FOR UPDATE', [
			':entityId' => $entity->getId(),
			':milestoneId' => $this->getId()
		]);

		if (false === $currentProgress) {
			throw new ItemNotFoundException('Unknown milestone status.');
		}

		if ($currentProgress == 100) {
			$conn->update(MilestoneTables::MILESTONE_STATUS_TBL, ['progress' => 0, 'completedAt' => null], ['entityId' => $entity->getId(), 'milestoneId' => $this->getId()]);
			$conn->executeUpdate('UPDATE `'.MilestoneTables::MILESTONE_PROGRESS_TBL.'` SET `completedNum` = (`completedNum` - 1) WHERE `entityId` = :entityId', [':entityId' => $entity->getId()]);
			if ($retVal == self::RETURN_BOOLEAN) {
				return true;
			} else {
				return $this->createStatusRow(0, null, $timeFormatter);
			}
		} else {
			return false;
		}
	}
	
	public function createStatusRow($progress, $completedAt, TimeFormatterInterface $timeFormatter, $editable = true)
	{
		return self::processStatus([
			'id' => $this->id,
			'name' => $this->name,
			'description' => $this->description,
			'deadline' => $this->deadline,
			'type' => $this->type,
			'progress' => $progress,
			'completedAt' => $completedAt
		], $timeFormatter, $editable);
	}
	
	public static function getProgressColor($progress): string
	{
		if ($progress < 50) {
			return 'danger';
			$result['badgeColor'] = 'red';
		} elseif ($progress < 80) {
			return 'warning';
			$result['badgeColor'] = 'orange';
		} else {
			return 'success';
			$result['badgeColor'] = 'green';
		}
	}
	
	public static function getBadgeColor($progress): string
	{
		if ($progress < 50) {
			return 'red';
		} elseif ($progress < 80) {
			return 'orange';
		} else {
			return 'green';
		}
	}
	
	public static function processStatus(array $row, TimeFormatterInterface $timeFormatter, $editable)
	{
		if (!$editable) {
			$row['actions'] = ['view']; 
		} else {
			if ($row['type'] == Milestone::TYPE_BINARY) {
				$row['actions'] = [ $row['progress'] == 100 ? 'cancel' : 'complete', 'view'];
			} else {
				$row['actions'] = [ 'update', 'view'];
			}
		}
		if (!empty($row['deadline'])) {
			$row['deadline'] = $timeFormatter->format(TimeFormatterInterface::FORMAT_DATE_LONG, $row['deadline']);
		} else {
			$row['deadline'] = '---';
		}
		if (!empty($row['completedAt'])) {
			$row['completedAt'] = $timeFormatter->format(TimeFormatterInterface::FORMAT_DATE_LONG, $row['completedAt']);
		} else {
			$row['completedAt'] = '---';
		}
		return $row;
	}
	
	private function createStatusPopulator(Connection $conn)
	{
		return $conn->prepare('INSERT INTO `'.MilestoneTables::MILESTONE_STATUS_TBL.'` (`entityId`, `milestoneId`, `progress`) VALUES(:entityId, :milestoneId, 0)');
	}
}