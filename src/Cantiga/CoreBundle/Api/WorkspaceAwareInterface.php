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
namespace Cantiga\CoreBundle\Api;

use Cantiga\CoreBundle\Api\Workspace;
use Symfony\Component\HttpFoundation\Request;

/**
 * Implemented by controllers that work in the context of a workspace. Upon the startup,
 * the controller may choose its workspace, which must be returned by the method provided
 * in this interface. This workspace instance is further configured and used in rendering.
 */
interface WorkspaceAwareInterface
{
	/**
	 * The method shall produce an instance of the concrete workspace.
	 */
	public function createWorkspace(Request $request): Workspace;
}
