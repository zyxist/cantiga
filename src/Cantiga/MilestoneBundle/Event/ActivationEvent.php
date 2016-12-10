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
namespace Cantiga\MilestoneBundle\Event;

use Cantiga\CoreBundle\Entity\Place;
use Cantiga\CoreBundle\Entity\Project;
use Symfony\Component\EventDispatcher\Event;

/**
 * The event can be sent to activate some milestone rule and change the status
 * of some milestone, when some change in the system occurs.
 *
 * @author Tomasz Jędrzejewski
 */
class ActivationEvent extends Event
{
	private $project;
	private $entity;
	private $activator;
	private $callback;
	
	/**
	 * Constructs the event. The callback must be a non-argument function that
	 * returns {@link \Cantiga\MilestoneBundle\Entity\NewMilestoneStatus} instance
	 * which describes the new status of the milestone configured by the user.
	 * 
	 * @param \Cantiga\MilestoneBundle\Entity\Project $project
	 * @param Place $entity
	 * @param string $activator
	 * @param callback $callback
	 */
	public function __construct(Project $project, Place $entity, $activator, $callback)
	{
		$this->project = $project;
		$this->entity = $entity;
		$this->activator = $activator;
		$this->callback = $callback;
	}
	
	/**
	 * @return Project
	 */
	public function getProject()
	{
		return $this->project;
	}
	
	/**
	 * @return Place
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	public function getActivator()
	{
		return $this->activator;
	}

	public function getCallback()
	{
		return $this->callback;
	}
}
