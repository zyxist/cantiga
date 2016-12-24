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
declare(strict_types=1);
namespace Cantiga\CoreBundle\Entity\Traits;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Place;
use Cantiga\Metamodel\DataMappers;

/**
 * Projects, groups and areas share the same reference to a generic place row
 * which is used e.g. as a slug storage, and for membership management.
 */
trait PlaceTrait
{
	/**
	 * @var Place
	 */
	protected $place;
	
	public function getPlace(): Place
	{
		return $this->place;
	}

	public function setPlace(Place $place): self
	{
		DataMappers::noOverwritingField($this->place);
		$this->place = $place;
		return $this;
	}
	
	protected static function createPlaceFieldList()
	{
		return 'e.`id` AS `place_id`, e.`name` AS `place_name`, e.`type` AS `place_type`, e.`removedAt` AS `place_removedAt`, e.`rootPlaceId` AS `place_rootPlaceId`, e.`archived` AS `place_archived`  ';
	}
	
	protected static function createPlaceJoin($primaryAlias)
	{
		return 'INNER JOIN `'.CoreTables::PLACE_TBL.'` e ON e.`id` = '.$primaryAlias.'.`placeId` ';
	}
}
