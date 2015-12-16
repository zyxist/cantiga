<?php
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
