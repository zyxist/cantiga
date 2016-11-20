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

/**
 * System language that can be chosen by the user.
 *
 * @author Tomasz Jędrzejewski
 */
class Language implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	private $id;
	private $name;
	private $locale;

	public static function fromArray($array, $prefix = '')
	{
		$item = new Language;
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
	
	public function getName()
	{
		return $this->name;
	}

	public function getLocale()
	{
		return $this->locale;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function setLocale($locale)
	{
		$this->locale = $locale;
		return $this;
	}
	
	public function insert(Connection $conn)
	{
		$conn->insert(
			CoreTables::LANGUAGE_TBL,
			DataMappers::pick($this, ['name', 'locale'])
		);
		return $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		return $conn->update(
			CoreTables::LANGUAGE_TBL,
			DataMappers::pick($this, ['name', 'locale']),
			DataMappers::pick($this, ['id'])
		);
	}
	
	public function canRemove()
	{
		return true;
	}
	
	public function remove(Connection $conn)
	{
		$conn->delete(CoreTables::LANGUAGE_TBL, DataMappers::pick($this, ['id']));
	}
}
