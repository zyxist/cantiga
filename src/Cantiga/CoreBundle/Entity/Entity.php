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
namespace Cantiga\CoreBundle\Entity;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Doctrine\DBAL\Connection;

/**
 * Generic database representation of any entity. We create such a row in order
 * to support functionalities that can be bound to the entities of different types.
 * For example, the task list can be bound both to group and area. Each entity that
 * should be managed in this way, should also deal with creating and managing such a
 * row.
 */
class Entity implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	private $id;
	private $name;
	private $slug;
	private $type;
	private $removedAt;
	
	public static function fetchById(Connection $conn, $id)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.CoreTables::ENTITY_TBL.'` WHERE `id` = :id', [':id' => $id]);
		if (false === $data) {
			return false;
		}
		return Entity::fromArray($data);
	}
	
	public static function fetchBySlug(Connection $conn, $slug)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.CoreTables::ENTITY_TBL.'` WHERE `slug` = :slug', [':slug' => $slug]);
		if (false === $data) {
			return false;
		}
		return Entity::fromArray($data);
	}
	
	public static function fromArray($array, $prefix = '')
	{
		$item = new Entity;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	function getSlug(): string
	{
		return $this->slug;
	}

	function setSlug($slug)
	{
		$this->slug = $slug;
	}

	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	
	public function getRemovedAt()
	{
		return $this->removedAt;
	}

	public function setRemovedAt($removedAt)
	{
		$this->removedAt = $removedAt;
		return $this;
	}

	public function canRemove()
	{
		return true;
	}

	public function insert(Connection $conn)
	{
		$conn->insert(
			CoreTables::ENTITY_TBL,
			DataMappers::pick($this, ['name', 'slug', 'type'])
		);
		return $this->id = $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		return $conn->update(
			CoreTables::ENTITY_TBL,
			DataMappers::pick($this, ['name']),
			DataMappers::pick($this, ['id'])
		);
	}
	
	public function remove(Connection $conn)
	{
		$this->removedAt = time();
		return $conn->update(
			CoreTables::ENTITY_TBL,
			DataMappers::pick($this, ['removedAt']),
			DataMappers::pick($this, ['id'])
		);
	}
}
