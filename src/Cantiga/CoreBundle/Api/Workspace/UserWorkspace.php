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
use Cantiga\CoreBundle\Event\CantigaEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * Admin workspace is a workspace, where the user can manage the system.
 *
 * @author Tomasz Jędrzejewski
 */
class UserWorkspace extends Workspace
{
	public function getKey()
	{
		return 'user';
	}
	
	public function isAvailable()
	{
		return true;
	}

	public function getWorkspaceEvent()
	{
		return CantigaEvents::WORKSPACE_USER;
	}
	
	public function getHelpPages(RouterInterface $router)
	{
		return [
			['route' => 'user_help_introduction', 'url' => $router->generate('user_help_introduction'), 'title' => 'Introduction to the system'],
			['route' => 'user_help_profile', 'url' => $router->generate('user_help_profile'), 'title' => 'User profile'],
			['route' => 'user_help_requesting_areas', 'url' => $router->generate('user_help_requesting_areas'), 'title' => 'Requesting areas'],
			['route' => 'user_help_invitations', 'url' => $router->generate('user_help_invitations'), 'title' => 'Invitations']
		];
	}
}
