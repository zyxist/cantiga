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
namespace Cantiga\CoreBundle\Api\Controller;

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\Components\Workspace\WorkspaceAwareInterface;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Api\Workspace\AdminWorkspace;

/**
 * Base class for all the controllers in the admin workspace.
 */
class AdminPageController extends CantigaController implements WorkspaceAwareInterface
{
	private $workspace;
	
	public function createWorkspace(Membership $membership = null): Workspace
	{
		return $this->workspace = new AdminWorkspace();
	}

	public function getWorkspace(): AdminWorkspace
	{
		return $this->workspace;
	}
}
