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

use Doctrine\DBAL\Connection;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;

class AppText implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	private $id;
	private $place;
	private $title;
	private $content;
	private $locale;
	private $project;
	
	private $isEmpty = false;
	
	/**
	 * @param Connection $conn
	 * @param int $id
	 * @return AppText
	 */
	public static function fetchById(Connection $conn, $id, Project $project = null)
	{
		if (null === $project) {
			$data = $conn->fetchAssoc('SELECT * FROM `'.CoreTables::APP_TEXT_TBL.'` WHERE `id` = :id AND `projectId` IS NULL', [':id' => $id]);
		} else {
			$data = $conn->fetchAssoc('SELECT * FROM `'.CoreTables::APP_TEXT_TBL.'` WHERE `id` = :id AND `projectId` = :projectId', [':id' => $id, ':projectId' => $project->getId()]);
		}
		
		if(false === $data) {
			return false;
		}
		$item = self::fromArray($data);
		if (null !== $project) {
			$item->setProject($project);
		}
		return $item;
	}
	
	/**
	 * @param Connection $conn
	 * @param string $place
	 * @param string $locale
	 * @param Project $project
	 * @return AppText
	 */
	public static function fetchByLocation(Connection $conn, $place, $locale, Project $project = null)
	{
		if (null === $project) {
			$data = $conn->fetchAssoc('SELECT * FROM `'.CoreTables::APP_TEXT_TBL.'` WHERE `place` = :place AND `locale` = :locale AND `projectId` IS NULL', [':place' => $place, ':locale' => $locale]);
		} else {
			$items = $conn->fetchAll('SELECT * FROM `'.CoreTables::APP_TEXT_TBL.'` '
				. 'WHERE `place` = :place AND `locale` = :locale AND (`projectId` = :projectId OR `projectId` IS NULL)', [':place' => $place, ':locale' => $locale, ':projectId' => $project->getId()]);
			$data = false;
			foreach ($items as $item) {
				if ($item['projectId'] == $project->getId()) {
					$data = $item;
					break;
				}
			}
			if (false === $data) {
				foreach ($items as $item) {
					if (empty($item['projectId'])) {
						$data = $item;
						break;
					}
				}
			}
		}
		
		
		if(false === $data) {
			return false;
		}
		$item = self::fromArray($data);
		if (null !== $project) {
			$item->setProject($project);
		}
		return $item;
	}

	public static function fromArray($array, $prefix = '')
	{
		$item = new AppText;
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
	
	public function getProject()
	{
		return $this->project;
	}

	public function setProject($project)
	{
		$this->project = $project;
		return $this;
	}
	
	public function getPlace()
	{
		return $this->place;
	}

	public function setPlace($place)
	{
		$this->place = $place;
		return $this;
	}
	
	public function getTitle()
	{
		return $this->title;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getLocale()
	{
		return $this->locale;
	}

	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	public function setLocale($locale)
	{
		$this->locale = $locale;
		return $this;
	}
	
	/**
	 * Indicates that the user-defined text has not been found in the database, and the
	 * default entity is returned.
	 * 
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return $this->isEmpty;
	}
	
	public function markEmpty()
	{
		$this->isEmpty = true;
	}

	public function insert(Connection $conn)
	{
		$conn->insert(
			CoreTables::APP_TEXT_TBL,
			DataMappers::pick($this, ['place', 'title', 'project', 'content', 'locale'])
		);
		return $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		return $conn->update(
			CoreTables::APP_TEXT_TBL,
			DataMappers::pick($this, ['place', 'title', 'content', 'locale']),
			DataMappers::pick($this, ['id'])
		);
	}
	
	public function remove(Connection $conn)
	{
		$conn->delete(CoreTables::APP_TEXT_TBL, DataMappers::pick($this, ['id']));
	}
	
	public function canRemove()
	{
		return true;
	}
}