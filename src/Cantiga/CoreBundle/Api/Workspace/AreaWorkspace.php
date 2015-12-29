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
use Cantiga\CoreBundle\Entity\Membership\AreaMembershipLoader;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\CoreBundle\Exception\AreasNotSupportedException;
use Cantiga\Metamodel\Membership;
use Symfony\Component\Routing\RouterInterface;

/**
 * Area workspace is a workspace, where the users work in the context of some area.
 * The user must be a member of some area in order to access it.
 * 
 * @see Cantiga\CoreBundle\Entity\Area
 * @author Tomasz Jędrzejewski
 */
class AreaWorkspace extends Workspace
{
	/**
	 * @var AreaMembershipLoader 
	 */
	private $areaMembershipLoader;
	/**
	 * @var string
	 */
	private $slug;
	/**
	 * @var Project
	 */
	private $project;
	
	public function __construct(AreaMembershipLoader $pml)
	{
		$this->areaMembershipLoader = $pml;
	}

	public function getKey()
	{
		return 'area';
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
		return $this->areaMembershipLoader;
	}

	public function getWorkspaceEvent()
	{
		return CantigaEvents::WORKSPACE_AREA;
	}
	
	public function onWorkspaceLoaded(Membership $membership)
	{
		$this->slug = $membership->getItem()->getSlug();
		$this->project = $membership->getItem()->getProject();
		if (!$this->project->getAreasAllowed()) {
			throw new AreasNotSupportedException('This project does not support areas.');
		}
	}
	
	public function getHelpPages(RouterInterface $router)
	{
		return [
			['route' => 'user_help_introduction', 'url' => $router->generate('user_help_introduction'), 'title' => 'Introduction to the system'],
			['route' => 'area_help_introduction', 'url' => $router->generate('area_help_introduction', ['slug' => $this->slug]), 'title' => 'Introduction to areas'],
			['route' => 'area_help_members', 'url' => $router->generate('area_help_members', ['slug' => $this->slug]), 'title' => 'Member management'],
		];
	}
}
