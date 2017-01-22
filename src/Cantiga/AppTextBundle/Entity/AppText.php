<?php
/*
 * This file is part of Cantiga Project. Copyright 2016-2017 Cantiga contributors.
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
 * along with Cantiga; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
declare(strict_types=1);
namespace Cantiga\AppTextBundle\Entity;

use Cantiga\Components\Hierarchy\HierarchicalInterface;
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

	public static function fetchById(Connection $conn, int $id, HierarchicalInterface $project = null)
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

	public static function fromArray(array $array, string $prefix = ''): AppText
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

	public function getProject(): ?HierarchicalInterface
	{
		return $this->project;
	}

	public function setProject(?HierarchicalInterface $project): self
	{
		$this->project = $project;
		return $this;
	}

	public function getPlace(): ?string
	{
		return $this->place;
	}

	public function setPlace(?string $place): self
	{
		$this->place = $place;
		return $this;
	}

	public function getTitle(): ?string
	{
		return $this->title;
	}

	public function getContent(): ?string
	{
		return $this->content;
	}

	public function getLocale(): ?string
	{
		return $this->locale;
	}

	public function setTitle(?string $title): self
	{
		$this->title = $title;
		return $this;
	}

	public function setContent(?string $content): self
	{
		$this->content = $content;
		return $this;
	}

	public function setLocale(?string $locale): self
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

	public function markEmpty(): void
	{
		$this->isEmpty = true;
	}

	public function insert(Connection $conn): int
	{
		$conn->insert(
			CoreTables::APP_TEXT_TBL,
			DataMappers::pick($this, ['place', 'title', 'project', 'content', 'locale'])
		);
		return (int) $conn->lastInsertId();
	}

	public function update(Connection $conn): int
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

	public function canRemove(): bool
	{
		return true;
	}
}
