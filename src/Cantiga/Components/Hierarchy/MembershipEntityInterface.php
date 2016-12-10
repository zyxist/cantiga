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
namespace Cantiga\Components\Hierarchy;

use Cantiga\Components\Hierarchy\Entity\MembershipRole;
use Cantiga\Components\Hierarchy\MembershipRoleResolverInterface;
use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Doctrine\DBAL\Connection;

interface MembershipEntityInterface extends IdentifiableInterface
{
	/**
	 * The method shall return all the members of the current place, as the entities of
	 * {@link Cantiga\Components\Hierarchy\Entity\Member} type.
	 * 
	 * @param Connection $conn Database connection
	 * @param MembershipRoleResolverInterface $roleResolver Decodes roles into meaningful entities
	 * @return array
	 */
	public function findMembers(Connection $conn, MembershipRoleResolverInterface $roleResolver): array;
	
	/**
	 * Finds a profile of the member of this place.
	 * 
	 * @param Connection $conn
	 * @param MembershipRoleResolverInterface $roleResolver
	 * @param int $id Member profile ID
	 */
	public function findMember(Connection $conn, MembershipRoleResolverInterface $roleResolver, int $id);
	public function joinMember(Connection $conn, CantigaUserRefInterface $user, MembershipRole $role, $note, $showDownstreamContactData);
	public function editMember(Connection $conn, CantigaUserRefInterface $user, MembershipRole $role, $note, $showDownstreamContactData);
	public function removeMember(Connection $conn, CantigaUserRefInterface $user);
}
