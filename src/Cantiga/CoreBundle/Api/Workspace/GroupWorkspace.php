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
namespace Cantiga\CoreBundle\Api\Workspace;

use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Entity\Membership\GroupMembershipLoader;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\Metamodel\Membership;
use Symfony\Component\Routing\RouterInterface;

/**
 * Group workspace is a workspace, where the users work in the context of some group.
 * The user must be a member of some group in order to access it.
 * 
 * @see Cantiga\CoreBundle\Entity\Area
 * @author Tomasz Jędrzejewski
 */
class GroupWorkspace extends Workspace
{
	/**
	 * @var GroupMembershipLoader 
	 */
	private $groupMembershipLoader;
	private $slug;
	
	public function __construct(GroupMembershipLoader $pml)
	{
		$this->groupMembershipLoader = $pml;
	}

	public function getKey()
	{
		return 'group';
	}
	
	public function getMembershipLoader()
	{
		return $this->groupMembershipLoader;
	}

	public function getWorkspaceEvent()
	{
		return CantigaEvents::WORKSPACE_GROUP;
	}
	
	public function onWorkspaceLoaded(Membership $membership)
	{
		$this->slug = $membership->getItem()->getSlug();
	}
	
	public function getHelpPages(RouterInterface $router)
	{
		return [
			['route' => 'user_help_introduction', 'url' => $router->generate('user_help_introduction'), 'title' => 'Introduction to the system'],
			['route' => 'group_help_introduction', 'url' => $router->generate('group_help_introduction', ['slug' => $this->slug]), 'title' => 'Introduction to groups'],
			['route' => 'group_help_members', 'url' => $router->generate('group_help_members', ['slug' => $this->slug]), 'title' => 'Member management'],
		];
	}
}
