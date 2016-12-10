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
namespace Cantiga\Metamodel;

use Cantiga\Components\Hierarchy\Entity\MembershipRole;
use Cantiga\Components\Hierarchy\MembershipRoleResolverInterface;
use LogicException;

/**
 * Repository for all the available membership roles for different item types.
 * The membership roles are registered by modules and bundles, and are not kept
 * in the database.
 */
class MembershipRoleResolver implements MembershipRoleResolverInterface
{	
	private $roles;
	
	public function __construct()
	{
		// TODO: Make some more generic configuration in the future and move this out of here.
		$this->roles['Project'][0] = new MembershipRole(0, 'Visitor', 'PLACE_VISITOR');
		$this->roles['Project'][1] = new MembershipRole(1, 'Member', 'PLACE_MEMBER', $this->roles['Project'][0]);
		$this->roles['Project'][2] = new MembershipRole(2, 'Manager', 'PLACE_MANAGER', $this->roles['Project'][1]);
		
		$this->roles['Group'][0] = new MembershipRole(0, 'Member', 'PLACE_MEMBER');
		
		$this->roles['Area'][0] = new MembershipRole(0, 'Member', 'PLACE_MEMBER');
		$this->roles['Area'][1] = new MembershipRole(1, 'Personal data access', 'PLACE_PD_ADMIN', $this->roles['Area'][0]);
		$this->roles['Area'][2] = new MembershipRole(2, 'Manager', 'PLACE_MANAGER', $this->roles['Area'][1]);
	}

	public function registerRole(string $itemType, MembershipRole $role)
	{
		if (!isset($this->roles[$itemType])) {
			$this->roles[$itemType] = array();
		}
		$this->roles[$itemType][$role->getId()] = $role;
	}

	public function getRoles(string $itemType): array
	{
		if (!isset($this->roles[$itemType])) {
			throw new LogicException('Invalid item type for fetching roles: '.$itemType);
		}
		return $this->roles[$itemType];
	}
	
	public function getRole(string $itemType, int $id): MembershipRole
	{
		if (!isset($this->roles[$itemType][$id])) {
			return new MembershipRole(-1, 'Unknown', 'USER');
		}
		return $this->roles[$itemType][$id];
	}
	
	public function getHighestRole(string $itemType): MembershipRole
	{
		if (!isset($this->roles[$itemType])) {
			return new MembershipRole(-1, 'Unknown', 'USER');
		}
		$highest = null;
		foreach ($this->roles[$itemType] as $role) {
			$highest = $role;
		}
		return $highest;
	}
	
	public function hasRole(string $itemType, int $id): bool
	{
		return isset($this->roles[$itemType][$id]);
	}
}
