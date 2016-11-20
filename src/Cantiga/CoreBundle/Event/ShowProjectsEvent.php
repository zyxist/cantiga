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

namespace Cantiga\CoreBundle\Event;

use Cantiga\Metamodel\ProjectRepresentation;
use Symfony\Component\EventDispatcher\Event;

/**
 * Emitted by the navbar controller, the event is used to pass the list of projects
 * to the UI. Note that this does not have to mean Cantiga projects. Areas in the area
 * workspace are handled in the same way.
 *
 * @author Tomasz Jędrzejewski
 */
class ShowProjectsEvent extends Event
{

	/**
	 * Stores the list of projects
	 * @var array
	 */
	private $projects = array();

	/**
	 * The active project is shown in as the list title.
	 * @var ProjectRepresentation
	 */
	private $activeProject;

	public function setActiveProject(ProjectRepresentation $project)
	{
		$this->activeProject = $project;
	}

	public function addProject(ProjectRepresentation $project)
	{
		$this->projects[] = $project;
	}

	public function hasActiveProject()
	{
		return null !== $this->activeProject;
	}

	public function getProjects()
	{
		return $this->projects;
	}

	public function getActiveProject()
	{
		return $this->activeProject;
	}

	public function hasProjects()
	{
		return sizeof($this->projects) > 0;
	}

}
