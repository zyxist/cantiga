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
use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointFilter;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Api\Workspace\GroupWorkspace;
use Cantiga\CoreBundle\Entity\Project;


/**
 * This class shall be extended by all the controllers that work in the
 * group workspace.
 */
class GroupPageController extends CantigaController implements WorkspaceAwareInterface, ProjectAwareControllerInterface
{
	/**
	 * @var GroupWorkspace
	 */
	private $workspace;
	/**
	 * @var ExtensionPointsFilter
	 */
	private $extensionFilter;
	
	private $slug;
	
	public function createWorkspace(Membership $membership = null): Workspace
	{
		$this->slug = $membership->getPlace()->getSlug();
		return $this->workspace = new GroupWorkspace($membership);
	}
	
	public function getWorkspace(): GroupWorkspace
	{
		return $this->workspace;
	}
	
	public function getSlug(): string
	{
		return $this->slug;
	}

	public function getActiveProject(): Project
	{
		return $this->workspace->getProject();
	}

	/**
	 * @return ExtensionPointFilter
	 */
	public function getExtensionPointFilter()
	{
		if (null === $this->extensionFilter) {
			$this->extensionFilter = $this->getActiveProject()->createExtensionPointFilter();
		}
		return $this->extensionFilter;
	}
	
	/**
	 * Shortcut for instantiating an extension point from the project settings.
	 * 
	 * @param string $extensionPoint Name of the extension point
	 * @param string $setting Name of the project setting which contains the name of the selected implementation
	 * @return object
	 */
	public function extensionPointFromSettings($extensionPoint, $setting)
	{
		return $this->getExtensionPoints()
			->getImplementation($extensionPoint, 
				$this->getExtensionPointFilter()->fromSettings($this->get('cantiga.project.settings'), $setting)
			);
	}
}
