<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Tomasz Jedrzejewski.
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

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\DiscussionBundle\Database\DiscussionAdapter;
use Cantiga\DiscussionBundle\DiscussionTables;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\MembershipEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Doctrine\DBAL\Connection;

class Channel implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
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
	private $subchannelLevel;
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
	
	function getProject()
	{
		return $this->project;
	}

	function getName()
	{
		return $this->name;
	}

	function getDescription()
	{
		return $this->description;
	}

	function getProjectVisible()
	{
		return $this->projectVisible;
	}

	function getGroupVisible()
	{
		return $this->groupVisible;
	}

	function getAreaVisible()
	{
		return $this->areaVisible;
	}

	function getProjectPosting()
	{
		return $this->projectPosting;
	}

	function getGroupPosting()
	{
		return $this->groupPosting;
	}

	function getAreaPosting()
	{
		return $this->areaPosting;
	}

	function getSubchannelLevel()
	{
		return $this->subchannelLevel;
	}

	function getEnabled()
	{
		return $this->enabled;
	}

	public function setId($id)
	{
		DataMappers::noOverwritingId($this->id);
		$this->id = $id;
		return $this;
	}
	
	function setProject($project)
	{
		$this->project = $project;
	}

	function setName($name)
	{
		$this->name = $name;
	}

	function setDescription($description)
	{
		$this->description = $description;
	}

	function setProjectVisible($projectVisible)
	{
		$this->projectVisible = (bool) $projectVisible;
	}

	function setGroupVisible($groupVisible)
	{
		$this->groupVisible = (bool) $groupVisible;
	}

	function setAreaVisible($areaVisible)
	{
		$this->areaVisible = (bool) $areaVisible;
	}

	function setProjectPosting($projectPosting)
	{
		$this->projectPosting = (bool) $projectPosting;
	}

	function setGroupPosting($groupPosting)
	{
		$this->groupPosting = (bool) $groupPosting;
	}

	function setAreaPosting($areaPosting)
	{
		$this->areaPosting = (bool) $areaPosting;
	}

	function setSubchannelLevel($subchannelLevel)
	{
		$this->subchannelLevel = (int) $subchannelLevel;
	}

	function setEnabled($enabled)
	{
		$this->enabled = (bool) $enabled;
	}
	
	function getLastPostTime()
	{
		return $this->lastPostTime;
	}

	function getColor()
	{
		return $this->color;
	}

	function getIcon()
	{
		return $this->icon;
	}

	function setColor($color)
	{
		$this->color = $color;
	}

	function setIcon($icon)
	{
		$this->icon = $icon;
	}
	
	public function isVisible(MembershipEntityInterface $entity): bool
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
	
	public function canRemove()
	{
		return true;
	}

	public function insert(Connection $conn)
	{
		$conn->insert(
			DiscussionTables::DISCUSSION_CHANNEL_TBL,
			DataMappers::pick($this, ['project', 'name', 'description', 'color', 'icon', 'projectVisible', 'groupVisible', 'areaVisible', 'projectPosting', 'groupPosting', 'areaPosting', 'separate', 'enabled'])
		);
		return $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		return $conn->update(
			DiscussionTables::DISCUSSION_CHANNEL_TBL,
			DataMappers::pick($this, ['name', 'description', 'color', 'icon', 'projectVisible', 'groupVisible', 'areaVisible', 'projectPosting', 'groupPosting', 'areaPosting', 'separate', 'enabled']),
			DataMappers::pick($this, ['id'])
		);
	}
	
	public function remove(Connection $conn)
	{
		$conn->delete(DiscussionTables::DISCUSSION_CHANNEL_TBL, DataMappers::pick($this, ['id']));
	}
}
