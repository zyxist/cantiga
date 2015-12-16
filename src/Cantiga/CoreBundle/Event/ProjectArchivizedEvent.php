<?php
namespace Cantiga\CoreBundle\Event;
use Symfony\Component\EventDispatcher\Event;
use Cantiga\CoreBundle\Entity\Project;

/**
 * By attaching to this event, you can perform additional action on the database, when
 * the specified project is getting archivized. The action is executed in the CLI environment
 * and within the transaction block.
 *
 * @author Tomasz Jędrzejewski
 */
class ProjectArchivizedEvent extends Event
{
	private $project;
	
	public function __construct(Project $project)
	{
		$this->project = $project;
	}
	
	/**
	 * @return Project
	 */
	public function getProject()
	{
		return $this->project;
	}
}
