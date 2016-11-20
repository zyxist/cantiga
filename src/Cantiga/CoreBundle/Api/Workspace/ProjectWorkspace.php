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
use Cantiga\CoreBundle\Entity\Membership\ProjectMembershipLoader;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\Metamodel\Membership;
use Symfony\Component\Routing\RouterInterface;

/**
 * Project workspace is a workspace, where the users work in the context of some project.
 * The user must be a member of some project in order to access it.
 * 
 * @see Cantiga\CoreBundle\Entity\Project
 * @author Tomasz Jędrzejewski
 */
class ProjectWorkspace extends Workspace
{
	/**
	 * @var ProjectMembershipLoader 
	 */
	private $projectMembershipLoader;
	/**
	 * @var Project
	 */
	private $project;
	
	public function __construct(ProjectMembershipLoader $pml)
	{
		$this->projectMembershipLoader = $pml;
	}

	public function getKey()
	{
		return 'project';
	}
	
	public function onWorkspaceLoaded(Membership $membership)
	{
		$this->project = $membership->getItem();
	}
	
	/**
	 * @return Project
	 */
	public function getProject()
	{
		return $this->project;
	}
	
	public function getMembershipLoader()
	{
		return $this->projectMembershipLoader;
	}

	public function getWorkspaceEvent()
	{
		return CantigaEvents::WORKSPACE_PROJECT;
	}
	
	public function getHelpPages(RouterInterface $router)
	{
		return [
			['route' => 'user_help_introduction', 'url' => $router->generate('user_help_introduction'), 'title' => 'Introduction to the system'],
			['route' => 'project_help_introduction', 'url' => $router->generate('project_help_introduction', ['slug' => $this->project->getSlug()]), 'title' => 'Introduction to projects'],
			['route' => 'project_help_members', 'url' => $router->generate('project_help_members', ['slug' => $this->project->getSlug()]), 'title' => 'Member management'],
		];
	}
}
