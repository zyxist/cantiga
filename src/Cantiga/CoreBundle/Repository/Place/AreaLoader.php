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
namespace Cantiga\CoreBundle\Repository\Place;

use Cantiga\Components\Hierarchy\Entity\PlaceRef;
use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\Components\Hierarchy\PlaceLoaderInterface;
use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Doctrine\DBAL\Connection;

class AreaLoader implements PlaceLoaderInterface
{
	private $conn;
	
	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}
	
	public function loadPlace(PlaceRef $place): HierarchicalInterface
	{
		$area = Area::fetchByPlaceRef($this->conn, $place);
		if (false === $area) {
			throw new ItemNotFoundException('No such area.', $place->getId());
		}
		return $area;
	}
	
	public function loadPlaceById(int $id): HierarchicalInterface
	{
		$area = Area::fetchActive($this->conn, $id);
		if (false === $area) {
			throw new ItemNotFoundException('No such area.', $id);
		}
		return $area;
	}
	
	public function loadPlaceForImport(HierarchicalInterface $currentPlace, CantigaUserRefInterface $member)
	{
		return Area::fetchForImport($this->conn, $currentPlace, $member);
	}
}
