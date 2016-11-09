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
use Cantiga\CoreBundle\Api\Controller\ProjectAwareControllerInterface;
use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointFilter;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Api\WorkspaceAwareInterface;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\Membership;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * The controller that can dynamically choose a workspace (project, group or area), depending
 * on the slug.
 */
class WorkspaceController extends CantigaController implements WorkspaceAwareInterface, ProjectAwareControllerInterface
{
	/**
	 * @var Workspace
	 */
	private $workspace;
	/**
	 * @var ExtensionPointsFilter
	 */
	private $extensionFilter;
	/**
	 * @var TokenStorageInterface
	 */
	private $tokenStorage;
	
	public function createWorkspace(Request $request): Workspace
	{
		$slug = $request->get('slug', NULL);
		if (empty($slug)) {
			throw new \RuntimeException('No slug specified');
		}
		
		$this->tokenStorage = $this->get('security.token_storage');
		return $this->workspace = $this->get('cantiga.workspace.finder')->findWorkspace($slug);
	}
	
	public function getWorkspace()
	{
		return $this->workspace;
	}
	
	public function getSlug()
	{
		return $this->tokenStorage->getToken()->getMembershipEntity()->getSlug();
	}
	
	/**
	 * @return Membership
	 */
	public function getMembership()
	{
		return $this->tokenStorage->getToken()->getMembership();
	}
	
	/**
	 * @return Project
	 */
	public function getActiveProject()
	{
		return $this->tokenStorage->getToken()->getMembershipEntity();
	}
	
	/**
	 * @return ExtensionPointFilter
	 */
	public function getExtensionPointFilter()
	{
		if (null === $this->extensionFilter) {
			$this->extensionFilter = $this->get('security.token_storage')->getToken()->getMasterProject()->createExtensionPointFilter();
		}
		return new ExtensionPointFilter();
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