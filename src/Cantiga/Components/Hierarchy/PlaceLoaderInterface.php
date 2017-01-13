<?php
/*
 * This file is part of Cantiga Project. Copyright 2017 Cantiga contributors.
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
namespace Cantiga\Components\Hierarchy;

use Cantiga\Components\Hierarchy\Entity\PlaceRef;
use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;

/**
 * Generic interface for loading place entities of different types. Implementing this interface
 * is mandatory to add a support for new types of places.
 */
interface PlaceLoaderInterface
{
	/**
	 * Loads a place of the given type by its ID.
	 *
	 * @param  int                   $id Place ID
	 * @return HierarchicalInterface     Place entity
	 */
	public function loadPlaceById(int $id): HierarchicalInterface;
	/**
	 * Loads the place, using the place reference information (used e.g. by the membership information).
	 *
	 * @param  PlaceRef              $place Reference to the place
	 * @return HierarchicalInterface        Place entity
	 */
	public function loadPlace(PlaceRef $place): HierarchicalInterface;
	/**
	 * Cantiga projects may be a continuation of another archived project. In this case, it is possible to implement
	 * an import of settings from the corresponding archived place with the same name the user was member of. The method
	 * receives the current place as a reference point, and shall return the corresponding place in the archived parent
	 * project. The second argument is the current user.
	 *
	 * @param  HierarchicalInterface   $currentPlace Current place (import destination)
	 * @param  CantigaUserRefInterface $member       Current user
	 * @return ?HierarchicalInterface                Source place for import, if exists
	 */
	public function loadPlaceForImport(HierarchicalInterface $currentPlace, CantigaUserRefInterface $member): ?HierarchicalInterface;
}
