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
use Cantiga\Components\Hierarchy\MembershipEntityInterface;
use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;
use Cantiga\CoreBundle\Entity\Invitation;
use Cantiga\CoreBundle\Entity\User;

interface MembershipRepositoryInterface
{
	public function joinMember(MembershipEntityInterface $identifiable, CantigaUserRefInterface $user, MembershipRole $role, $note, $showDownstreamContactData);
	public function editMember(MembershipEntityInterface $identifiable, CantigaUserRefInterface $user, MembershipRole $role, $note, $showDownstreamContactData);
	public function removeMember(MembershipEntityInterface $identifiable, CantigaUserRefInterface $user);
	public function acceptInvitation(Invitation $invitation);
	public function clearMembership(User $user);
}
