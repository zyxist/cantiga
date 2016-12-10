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

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\CoreTexts;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Event\CantigaEvents;

/**
 * Group workspace is a workspace, where the users work in the context of some group.
 * The user must be a member of some group in order to access it.
 * 
 * @see Area
 */
class GroupWorkspace extends Workspace
{
	private $slug;
	/**
	 * @var Project
	 */
	private $project;
	
	public function __construct(Membership $membership)
	{
		$this->project = $membership->getPlace()->getRootElement();
		$this->slug = $membership->getPlace()->getPlace()->getSlug();
	}

	public function getKey()
	{
		return 'group';
	}
	
	/**
	 * @return Project
	 */
	public function getProject()
	{
		return $this->project;
	}

	public function getWorkspaceEvent()
	{
		return CantigaEvents::WORKSPACE_GROUP;
	}
	
	public function getHelpRoute(): string
	{
		return 'place_help_page';
	}
	
	public function getHelpPages(): array
	{
		return [
			['route' => 'user_introduction', 'title' => 'Introduction to the system', 'text' => CoreTexts::HELP_INTRODUCTION],
			['route' => 'group_introduction', 'title' => 'Introduction to groups', 'text' => CoreTexts::HELP_GROUP_INTRODUCTION],
			['route' => 'group_members', 'title' => 'Member management', 'text' => CoreTexts::HELP_GROUP_MEMBERS],
		];
	}
}
