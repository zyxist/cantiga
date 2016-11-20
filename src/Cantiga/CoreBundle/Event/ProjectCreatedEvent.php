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

use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Settings\ProjectSettings;
use Symfony\Component\EventDispatcher\Event;

/**
 * Sent, when a new project is being created, and allows performing additional operations. The event action
 * is executed in the same transaction that creates the project.
 *
 * @author Tomasz Jędrzejewski
 */
class ProjectCreatedEvent extends Event
{
	private $project;
	private $settings;
	
	public function __construct(Project $project, ProjectSettings $settings)
	{
		$this->project = $project;
		$this->settings = $settings;
	}
	
	/**
	 * @return Project
	 */
	public function getProject()
	{
		return $this->project;
	}
	
	/**
	 * @return ProjectSettings
	 */
	public function getSettings()
	{
		return $this->settings;
	}
}
