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

/**
 * An abstraction over a service that knows the user roles and is able to
 * translate them from their ID-s into useful entities.
 */
interface MembershipRoleResolverInterface
{
	public function registerRole(string $itemType, MembershipRole $role);
	public function getRoles(string $itemType): array;
	public function getRole(string $itemType, int $id): MembershipRole;
	public function getHighestRole(string $itemType): MembershipRole;
	public function hasRole(string $itemType, int $id): bool;
}
