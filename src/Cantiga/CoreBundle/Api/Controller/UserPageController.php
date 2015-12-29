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
namespace Cantiga\CoreBundle\Api\Controller;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Workspace\UserWorkspace;
use Cantiga\CoreBundle\Api\WorkspaceAwareInterface;

/**
 * All the pages that work in the user workspace, shall use this controller.
 *
 * @author Tomasz Jędrzejewski
 */
class UserPageController extends CantigaController implements WorkspaceAwareInterface
{
	private $workspace;
	
	public function createWorkspace()
	{
		return $this->workspace = new UserWorkspace();
	}
	
	/**
	 * @return UserWorkspace
	 */
	public function getWorkspace()
	{
		return $this->workspace;
	}
	
	public function createProjectList()
	{
		return [];
	}
}
