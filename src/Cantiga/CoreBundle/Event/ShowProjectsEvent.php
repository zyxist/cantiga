<?php
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
