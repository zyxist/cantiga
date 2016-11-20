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
namespace Cantiga\CoreBundle\Entity\Traits;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Entity;
use Cantiga\Metamodel\DataMappers;
/**
 * Adds support for generic entities.
 *
 * @author Tomasz Jędrzejewski
 */
trait EntityTrait
{
	protected $entity;
	
	/**
	 * @return Entity
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	public function setEntity($entity)
	{
		DataMappers::noOverwritingField($this->entity);
		$this->entity = $entity;
		return $this;
	}
	
	protected static function createEntityFieldList()
	{
		return 'e.`id` AS `entity_id`, e.`name` AS `entity_name`, e.`type` AS `entity_type`, e.`removedAt` AS `entity_removedAt` ';
	}
	
	protected static function createEntityJoin($primaryAlias)
	{
		return 'INNER JOIN `'.CoreTables::ENTITY_TBL.'` e ON e.`id` = '.$primaryAlias.'.`entityId` ';
	}
}
