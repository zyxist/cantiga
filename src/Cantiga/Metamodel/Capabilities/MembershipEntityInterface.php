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
namespace Cantiga\Metamodel\Capabilities;

use Doctrine\DBAL\Connection;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\MembershipRole;
use Cantiga\Metamodel\MembershipRoleResolver;

/**
 * @author Tomasz Jędrzejewski
 */
interface MembershipEntityInterface extends IdentifiableInterface
{
	/**
	 * The method shall return all the members of the current entity, given as an array to present through JSON.
	 * 
	 * @param Connection $conn
	 * @return array
	 */
	public function findMembers(Connection $conn, MembershipRoleResolver $roleResolver);
	public function joinMember(Connection $conn, User $user, MembershipRole $role, $note);
	public function editMember(Connection $conn, User $user, MembershipRole $role, $note);
	public function removeMember(Connection $conn, User $user);
}
