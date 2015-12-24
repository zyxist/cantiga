<?php
namespace Cantiga\MilestoneBundle\EventListener;

use Cantiga\CoreBundle\Api\WorkItem;
use Cantiga\CoreBundle\Event\WorkspaceEvent;

/**
 * @author Tomasz Jędrzejewski
 */
class WorkspaceListener
{
	public function onProjectWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		if ($workspace->getProject()->supportsModule('milestone')) {
			$workspace->addWorkItem('manage', new WorkItem('project_milestone_index', 'Milestones'));
		}
	}
}
