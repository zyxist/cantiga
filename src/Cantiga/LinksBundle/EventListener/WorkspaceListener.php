<?php
namespace Cantiga\LinksBundle\EventListener;

use Cantiga\CoreBundle\Api\WorkItem;
use Cantiga\CoreBundle\Event\WorkspaceEvent;

/**
 * @author Tomasz Jędrzejewski
 */
class WorkspaceListener
{
	public function onAdminWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		$workspace->addWorkItem('settings', new WorkItem('admin_links_index', 'Important links'));
	}
	
	public function onProjectWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		if ($workspace->getProject()->supportsModule('links')) {
			$workspace->addWorkItem('manage', new WorkItem('project_links_index', 'Important links'));
		}
	}
}
