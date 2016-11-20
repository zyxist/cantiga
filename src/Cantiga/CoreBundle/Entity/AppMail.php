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

class AppMail implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	private $id;
	private $place;
	private $subject;
	private $content;
	private $locale;
	private $lastUpdate;
	
	/**
	 * @param Connection $conn
	 * @param int $id
	 * @return AppText
	 */
	public static function fetchById(Connection $conn, $id)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.CoreTables::MAIL_TBL.'` WHERE `id` = :id', [':id' => $id]);
		if(null === $data) {
			return false;
		}
		return self::fromArray($data);
	}

	public static function fromArray($array, $prefix = '')
	{
		$item = new AppMail;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
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
	
	public function getPlace()
	{
		return $this->place;
	}

	public function setPlace($place)
	{
		$this->place = $place;
		return $this;
	}
	
	public function getSubject()
	{
		return $this->subject;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getLocale()
	{
		return $this->locale;
	}

	public function setSubject($subject)
	{
		$this->subject = $subject;
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
	
	public function getLastUpdate()
	{
		return $this->lastUpdate;
	}

	public function setLastUpdate($lastUpdate)
	{
		DataMappers::noOverwritingField($this->lastUpdate);
		$this->lastUpdate = $lastUpdate;
		return $this;
	}
	
	public function insert(Connection $conn)
	{
		$conn->insert(
			CoreTables::MAIL_TBL,
			DataMappers::pick($this, ['place', 'subject', 'content', 'locale'])
		);
		return $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		$this->lastUpdate = time();
		return $conn->update(
			CoreTables::MAIL_TBL,
			DataMappers::pick($this, ['place', 'subject', 'content', 'locale', 'lastUpdate']),
			DataMappers::pick($this, ['id'])
		);
	}
	
	public function remove(Connection $conn)
	{
		$conn->delete(CoreTables::MAIL_TBL, DataMappers::pick($this, ['id']));
	}

	public function canRemove()
	{
		return true;
	}
}