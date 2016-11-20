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
namespace Cantiga\ExportBundle\Entity;

use Cantiga\CoreBundle\Entity\AreaStatus;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\ExportBundle\ExportTables;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Doctrine\DBAL\Connection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class DataExport implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	private $id;
	private $name;
	private $project;
	private $areaStatus;
	private $url;
	private $active;
	private $encryptionKey;
	private $notes;
	
	public static function fetchById(Connection $conn, $id)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.ExportTables::DATA_EXPORT_TBL.'` WHERE `id` = :id', [':id' => $id]);
		
		if (false === $data) {
			return false;
		}
		$item = self::fromArray($data);
		$item->project = Project::fetch($conn, $data['projectId']);
		$item->areaStatus = AreaStatus::fetchByProject($conn, $data['areaStatusId'], $item->project);
		return $item;
	}
	
	public static function fromArray($array, $prefix = '')
	{
		$item = new DataExport;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}

	public static function getRelationships()
	{
		return ['project', 'areaStatus'];
	}

	public static function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('name', new NotBlank());
		$metadata->addPropertyConstraint('name', new Length(['min' => 2, 'max' => 40]));
		$metadata->addPropertyConstraint('url', new NotBlank());
		$metadata->addPropertyConstraint('url', new Url);
		$metadata->addPropertyConstraint('url', new Length(['min' => 2, 'max' => 100]));
		$metadata->addPropertyConstraint('encryptionKey', new NotBlank());
		$metadata->addPropertyConstraint('encryptionKey', new Length(['min' => 2, 'max' => 128]));
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getProject()
	{
		return $this->project;
	}

	public function getAreaStatus()
	{
		return $this->areaStatus;
	}

	public function getActive()
	{
		return $this->active;
	}

	public function getEncryptionKey()
	{
		return $this->encryptionKey;
	}

	public function getNotes()
	{
		return $this->notes;
	}

	public function setId($id)
	{
		DataMappers::noOverwritingId($this->id);
		$this->id = $id;
		return $this;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function setProject($project)
	{
		$this->project = $project;
		return $this;
	}

	public function setAreaStatus($areaStatus)
	{
		$this->areaStatus = $areaStatus;
		return $this;
	}

	public function setActive($active)
	{
		$this->active = $active;
		return $this;
	}

	public function setEncryptionKey($encryptionKey)
	{
		$this->encryptionKey = $encryptionKey;
		return $this;
	}

	public function setNotes($notes)
	{
		$this->notes = $notes;
		return $this;
	}
	
	public function getUrl()
	{
		return $this->url;
	}

	public function setUrl($url)
	{
		$this->url = $url;
		return $this;
	}
	
	public function insert(Connection $conn)
	{
		$conn->insert(
			ExportTables::DATA_EXPORT_TBL,
			DataMappers::pick($this, ['name', 'project', 'areaStatus', 'url', 'encryptionKey', 'active', 'notes'])
		);
		return $this->id = $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		return $conn->update(
			ExportTables::DATA_EXPORT_TBL,
			DataMappers::pick($this, ['name', 'project', 'areaStatus', 'url', 'encryptionKey', 'active', 'notes']),
			DataMappers::pick($this, ['id'])
		);
	}

	public function canRemove()
	{
		return true;
	}
	
	public function remove(Connection $conn)
	{
		$conn->delete(ExportTables::DATA_EXPORT_TBL, DataMappers::pick($this, ['id']));
	}
}