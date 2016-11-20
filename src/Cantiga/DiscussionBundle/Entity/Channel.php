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
namespace Cantiga\DiscussionBundle\Entity;

use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\DiscussionBundle\DiscussionTables;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Doctrine\DBAL\Connection;

class Channel implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	const BY_PROJECT = 0;
	const BY_GROUP = 1;
	const BY_AREA = 2;
	
	private $id;
	private $project;
	private $name;
	private $description;
	private $lastPostTime;
	private $color;
	private $icon;
	private $projectVisible;
	private $groupVisible;
	private $areaVisible;
	private $projectPosting;
	private $groupPosting;
	private $areaPosting;
	private $discussionGrouping;
	private $enabled;
	
	public static function fetchByProject(Connection $conn, $id, Project $project): Channel
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.DiscussionTables::DISCUSSION_CHANNEL_TBL.'` WHERE `id` = :id AND `projectId` = :projectId', [':id' => $id, ':projectId' => $project->getId()]);
		if (empty($data)) {
			return false;
		}
		$item = self::fromArray($data);
		$item->project = $project;
		return $item;
	}
	
	public static function fromArray($array, $prefix = ''): Channel
	{
		$item = new Channel;
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

	public function getProjectVisible()
	{
		return $this->projectVisible;
	}

	public function getGroupVisible()
	{
		return $this->groupVisible;
	}

	public function getAreaVisible()
	{
		return $this->areaVisible;
	}

	public function getProjectPosting()
	{
		return $this->projectPosting;
	}

	public function getGroupPosting()
	{
		return $this->groupPosting;
	}

	public function getAreaPosting()
	{
		return $this->areaPosting;
	}

	public function getDiscussionGrouping()
	{
		return $this->discussionGrouping;
	}
	
	public function getDiscussionGroupingAsText(): string
	{
		return self::discussionGroupingText($this->discussionGrouping);
	}

	public function getEnabled()
	{
		return $this->enabled;
	}

	public function setId($id)
	{
		DataMappers::noOverwritingId($this->id);
		$this->id = $id;
		return $this;
	}
	
	public function setProject($project)
	{
		$this->project = $project;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setDescription($description)
	{
		$this->description = $description;
	}

	public function setProjectVisible($projectVisible)
	{
		$this->projectVisible = (bool) $projectVisible;
	}

	public function setGroupVisible($groupVisible)
	{
		$this->groupVisible = (bool) $groupVisible;
	}

	public function setAreaVisible($areaVisible)
	{
		$this->areaVisible = (bool) $areaVisible;
	}

	public function setProjectPosting($projectPosting)
	{
		$this->projectPosting = (bool) $projectPosting;
	}

	public function setGroupPosting($groupPosting)
	{
		$this->groupPosting = (bool) $groupPosting;
	}

	public function setAreaPosting($areaPosting)
	{
		$this->areaPosting = (bool) $areaPosting;
	}

	public function setDiscussionGrouping($discussionGrouping)
	{
		$this->discussionGrouping = (int) $discussionGrouping;
	}

	public function setEnabled($enabled)
	{
		$this->enabled = (bool) $enabled;
	}
	
	public function getLastPostTime()
	{
		return $this->lastPostTime;
	}

	public function getColor()
	{
		return $this->color;
	}

	public function getIcon()
	{
		return $this->icon;
	}

	public function setColor($color)
	{
		$this->color = $color;
	}

	public function setIcon($icon)
	{
		$this->icon = $icon;
	}
	
	public function isVisible(HierarchicalInterface $entity): bool
	{
		if ($entity instanceof Project) {
			return (bool) $this->projectVisible;
		} elseif ($entity instanceof Group) {
			return (bool) $this->groupVisible;
		} elseif ($entity instanceof Area) {
			return (bool) $this->areaVisible;
		}
		return false;
	}
	
	public function canPost(HierarchicalInterface $entity): bool
	{
		if (!$this->enabled) {
			return false;
		}
		if ($entity instanceof Project) {
			return (bool) $this->projectPosting;
		} elseif ($entity instanceof Group) {
			return (bool) $this->groupPosting;
		} elseif ($entity instanceof Area) {
			return (bool) $this->areaPosting;
		}
		return false;
	}
	
	public function canRemove()
	{
		return true;
	}

	public function insert(Connection $conn)
	{
		$conn->insert(
			DiscussionTables::DISCUSSION_CHANNEL_TBL,
			DataMappers::pick($this, ['project', 'name', 'description', 'color', 'icon', 'projectVisible', 'groupVisible', 'areaVisible', 'projectPosting', 'groupPosting', 'areaPosting', 'discussionGrouping', 'enabled'])
		);
		return $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		return $conn->update(
			DiscussionTables::DISCUSSION_CHANNEL_TBL,
			DataMappers::pick($this, ['name', 'description', 'color', 'icon', 'projectVisible', 'groupVisible', 'areaVisible', 'projectPosting', 'groupPosting', 'areaPosting', 'enabled']),
			DataMappers::pick($this, ['id'])
		);
	}
	
	public function remove(Connection $conn)
	{
		$conn->delete(DiscussionTables::DISCUSSION_CHANNEL_TBL, DataMappers::pick($this, ['id']));
	}
	
	public static function discussionGroupingText($grouping)
	{
		switch($grouping) {
			case self::BY_PROJECT:
				return 'none';
			case self::BY_GROUP:
				return 'by group';
			case self::BY_AREA:
				return 'by area';
		}
		return '';
	}
}
