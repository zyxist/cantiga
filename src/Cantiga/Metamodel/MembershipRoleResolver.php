<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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
namespace Cantiga\Metamodel;

use LogicException;

/**
 * Repository for all the available membership roles for different item types.
 * The membership roles are registered by modules and bundles, and are not kept
 * in the database.
 *
 * @author Tomasz Jędrzejewski
 */
class MembershipRoleResolver
{	
	private $roles;
	
	public function __construct()
	{
		// TODO: Make some more generic configuration in the future and move this out of here.
		$this->roles['Project'][0] = new MembershipRole(0, 'Visitor', 'ROLE_PROJECT_VISITOR');
		$this->roles['Project'][1] = new MembershipRole(1, 'Member', 'ROLE_PROJECT_MEMBER');
		$this->roles['Project'][2] = new MembershipRole(2, 'Manager', 'ROLE_PROJECT_MANAGER');
		
		$this->roles['Group'][0] = new MembershipRole(0, 'Member', 'ROLE_GROUP_MEMBER');
		
		$this->roles['Area'][0] = new MembershipRole(0, 'Member', 'ROLE_AREA_MEMBER');
		$this->roles['Area'][1] = new MembershipRole(1, 'Manager', 'ROLE_AREA_MANAGER');
	}

	public function registerRole($itemType, MembershipRole $role)
	{
		if (!isset($this->roles[$itemType])) {
			$this->roles[$itemType] = array();
		}
		$this->roles[$itemType][$role->getId()] = $role;
	}

	public function getRoles($itemType)
	{
		if (!isset($this->roles[$itemType])) {
			throw new LogicException('Invalid item type for fetching roles: '.$itemType);
		}
		return $this->roles[$itemType];
	}
	
	public function getRole($itemType, $id)
	{
		if (!isset($this->roles[$itemType][$id])) {
			return new MembershipRole(-1, 'Unknown', 'ROLE_USER');
		}
		return $this->roles[$itemType][$id];
	}
	
	public function getHighestRole($itemType)
	{
		if (!isset($this->roles[$itemType])) {
			return new MembershipRole(-1, 'Unknown', 'ROLE_USER');
		}
		$highest = null;
		foreach ($this->roles[$itemType] as $role) {
			$highest = $role;
		}
		return $highest;
	}
	
	public function hasRole($itemType, $id)
	{
		return isset($this->roles[$itemType][$id]);
	}
}
