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
namespace Cantiga\CoreBundle\Api\Workspace;

use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * Administrative workspace allows managing the system. It is available only to the
 * users with the 'ROLE_ADMIN' granted.
 *
 * @author Tomasz Jędrzejewski
 */
class AdminWorkspace extends Workspace
{
	public function getKey()
	{
		return 'admin';
	}
	
	public function isAvailable()
	{
		return true;
	}

	public function getWorkspaceEvent()
	{
		return CantigaEvents::WORKSPACE_ADMIN;
	}
	
	public function getHelpPages(RouterInterface $router)
	{
		return [
			['route' => 'user_help_introduction', 'url' => $router->generate('user_help_introduction'), 'title' => 'Introduction to the system'],
			['route' => 'admin_help_managing', 'url' => $router->generate('admin_help_managing'), 'title' => 'Managing the system']
		];
	}
}
