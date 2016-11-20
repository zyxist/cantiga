<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Tomasz Jedrzejewski.
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
namespace Cantiga\CoreBundle\Entity\Membership;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Components\Hierarchy\MembershipEntityInterface;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\MembershipLoaderInterface;
use Cantiga\Components\Hierarchy\MembershipRoleResolverInterface;
use Cantiga\Metamodel\ProjectRepresentation;
use Doctrine\DBAL\Connection;

/**
 * Loads the information about the areas the user is member of.
 */
class AreaMembershipLoader implements MembershipLoaderInterface
{
	/**
	 * @var Connection
	 */
	private $conn;
	/**
	 * @var MembershipRoleResolverInterface
	 */
	private $resolver;
	/**
	 * The user whose information is cached.
	 * @var int
	 */
	private $cachedUser = null;
	/**
	 * Cache, if the loading is called multiple times.
	 * @var array
	 */
	private $loadedItems;
	
	public function __construct(Connection $conn, MembershipRoleResolverInterface $resolver)
	{
		$this->conn = $conn;
		$this->resolver = $resolver;
	}
	
	public function findMembership($slug, User $user)
	{
		$membership = Area::fetchMembership($this->conn, $this->resolver, $slug, $user->getId());
		if (false === $membership) {
			throw new ItemNotFoundException('The specified project is not available.', $slug);
		}
		$user->addRole('ROLE_AREA_AWARE');
		return $membership;
	}

	public function findProjectForEntity(MembershipEntityInterface $entity)
	{
		return $entity->getProject();
	}
	
	public function loadProjectRepresentations(User $user)
	{
		if (empty($this->loadedItems)) {
			$this->loadedItems = $this->buildRepresentations($user);
			$this->cachedUser = $user->getId();
		} elseif ($this->cachedUser != $user->getId()) {
			return $this->buildRepresentations($user);
		}
		return $this->loadedItems;
	}

	private function buildRepresentations(User $user)
	{
		$projects = $this->conn->fetchAll('SELECT a.`id`, a.`name`, a.`slug`, c.`role`, c.`note` FROM `'.CoreTables::AREA_TBL.'` a '
			. 'INNER JOIN `'.CoreTables::AREA_MEMBER_TBL.'` c ON c.`areaId` = a.`id` '
			. 'WHERE c.`userId` = :userId ORDER BY a.`name`', [':userId' => $user->getId()]);
		$items = array();
		foreach ($projects as $proj) {
			$items[] = new ProjectRepresentation($proj['slug'], $proj['name'], 'area_dashboard', 'AreaNominative: 0', 'purple', $this->resolver->getRole('Area', $proj['role']), $proj['note']);
		}
		return $items;
	}
}
