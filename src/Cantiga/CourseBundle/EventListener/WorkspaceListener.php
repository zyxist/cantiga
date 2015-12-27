<?php
namespace Cantiga\CourseBundle\EventListener;

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
		if ($workspace->getProject()->supportsModule('course')) {
			$workspace->addWorkItem('manage', new WorkItem('project_course_index', 'Courses'));
		}
	}
}
