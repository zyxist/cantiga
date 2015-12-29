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

use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Capabilities\MembershipEntityInterface;
use Cantiga\Metamodel\Exception\ItemNotFoundException;

/**
 * This interface shall be implemented by membership information loaders for given entities.
 * Cantiga workspaces that operate in the context of projects, groups or areas, use this to
 * load the current entity, its project (the project determines used settings), and list of
 * all items of the given type the user is member of (to produce the menu).
 * 
 * @author Tomasz Jędrzejewski
 */
interface MembershipLoaderInterface
{
	/**
	 * Loads the membership aware entity for the given slug. Once this method is executed,
	 * the engine calls <tt>findProjectForEntity()</tt> to extract the project information
	 * from the loaded entity.
	 * 
	 * @param string $slug
	 * @param User $user Currently logged user
	 * @return Membership
	 * @throws ItemNotFoundException
	 */
	public function findMembership($slug, User $user);
	/**
	 * The method is guaranteed to receive the entity loaded by <tt>findMembershipAwareEntity()</tt>,
	 * and shall return a project associated to it.
	 * 
	 * @param \Cantiga\Metamodel\MembershipEntityInterface $entity Entity loaded by the previous method.
	 */
	public function findProjectForEntity(MembershipEntityInterface $entity);
	/**
	 * Fetches the list of items of the given type, the user is member of. The list will be used
	 * for generating the menu. The method shall return an array of {@link ProjectRepresentation}
	 * instances.
	 * 
	 * @param \Cantiga\Metamodel\User $user Currently logged user.
	 */
	public function loadProjectRepresentations(User $user);
}
