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

use Cantiga\Components\Hierarchy\Entity\PlaceRef;
use Symfony\Component\EventDispatcher\Event;

/**
 * Emitted by the navbar controller, the event is used to pass the list of projects
 * to the UI. Note that this does not have to mean Cantiga projects. Areas in the area
 * workspace are handled in the same way.
 */
class ShowProjectsEvent extends Event
{

	/**
	 * Stores the list of place
	 * @var array
	 */
	private $places = array();

	/**
	 * The active place is shown in as the list title.
	 * @var PlaceRef
	 */
	private $activePlace;
	
	private $showArchives = false;

	public function setActiveProject(PlaceRef $project)
	{
		$this->activePlace = $project;
	}

	public function addProject(PlaceRef $project)
	{
		$this->places[] = $project;
	}

	public function hasActiveProject()
	{
		return null !== $this->activePlace;
	}

	public function getProjects()
	{
		return $this->places;
	}

	public function getActiveProject()
	{
		return $this->activePlace;
	}

	public function hasProjects()
	{
		return sizeof($this->places) > 0;
	}

	public function setShowArchives(bool $value)
	{
		$this->showArchives = $value;
	}
	
	public function getShowArchives(): bool
	{
		return $this->showArchives;
	}
}
