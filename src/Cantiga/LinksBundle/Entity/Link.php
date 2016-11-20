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
namespace Cantiga\LinksBundle\Entity;

use Cantiga\CoreBundle\Entity\Project;
use Cantiga\LinksBundle\LinksTables;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Doctrine\DBAL\Connection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Link implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	const PRESENT_PROJECT = 0;
	const PRESENT_GROUP = 1;
	const PRESENT_AREA = 2;
	const PRESENT_USER = 3;
	const PRESENT_ADMIN = 4;
	
	private $id;
	private $name;
	private $url;
	private $project;
	private $presentedTo;
	private $listOrder;
	
	public static function fetchByProject(Connection $conn, $id, Project $project)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.LinksTables::LINK_TBL.'` WHERE `id` = :id AND `projectId` = :projectId', [':id' => $id, ':projectId' => $project->getId()]);
		if (empty($data)) {
			return false;
		}
		$item = self::fromArray($data);
		$item->project = $project;
		return $item;
	}
	
	public static function fetchUnassigned(Connection $conn, $id)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.LinksTables::LINK_TBL.'` WHERE `id` = :id AND `projectId` IS NULL', [':id' => $id]);
		if (empty($data)) {
			return false;
		}
		$item = self::fromArray($data);
		return $item;
	}

	public static function fromArray($array, $prefix = '')
	{
		$item = new Link;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public static function getRelationships()
	{
		return ['project'];
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata) {
		$metadata->addPropertyConstraint('name', new NotBlank());
		$metadata->addPropertyConstraint('name', new Length(['min' => 2, 'max' => 100]));
		$metadata->addPropertyConstraint('url', new NotBlank());
		$metadata->addPropertyConstraint('url', new Length(['min' => 10, 'max' => 100]));
		$metadata->addPropertyConstraint('presentedTo', new Range(['min' => 0, 'max' => 4]));
		$metadata->addPropertyConstraint('listOrder', new Range(['min' => 0]));
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
	
	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getListOrder()
	{
		return $this->listOrder;
	}

	public function setUrl($url)
	{
		$this->url = $url;
		return $this;
	}
	
	public function getPresentedTo()
	{
		return $this->presentedTo;
	}

	public function setPresentedTo($presentedTo)
	{
		$this->presentedTo = $presentedTo;
		return $this;
	}

	public function setListOrder($order)
	{
		$this->listOrder = $order;
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
	
	public static function presentedToText($status)
	{
		switch($status) {
			case self::PRESENT_ADMIN:
				return 'on admin dashboard';
			case self::PRESENT_USER:
				return 'on user dashboard';
			case self::PRESENT_PROJECT:
				return 'on project dashboard';
			case self::PRESENT_GROUP:
				return 'on group dashboard';
			case self::PRESENT_AREA:
				return 'on area dashboard';
		}
	}
	
	public function getPresentedToText()
	{
		return self::presentedToText($this->presentedTo);
	}

	public function insert(Connection $conn)
	{
		$conn->insert(
			LinksTables::LINK_TBL,
			DataMappers::pick($this, ['name', 'url', 'project', 'presentedTo', 'listOrder'])
		);
		return $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		return $conn->update(
			LinksTables::LINK_TBL,
			DataMappers::pick($this, ['name', 'url', 'presentedTo', 'listOrder']),
			DataMappers::pick($this, ['id'])
		);
	}
	
	public function canRemove()
	{
		return true;
	}
	
	public function remove(Connection $conn)
	{
		$conn->delete(LinksTables::LINK_TBL, DataMappers::pick($this, ['id']));
	}
}