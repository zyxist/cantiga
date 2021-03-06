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
			$workspace->addWorkItem('summary', new WorkItem('project_milestone_summary', 'Milestones'));
			$workspace->addWorkItem('data', new WorkItem('project_milestone_editor', 'Milestones'));
			$workspace->addWorkItem('manage', new WorkItem('project_milestone_index', 'Milestones'));
			$workspace->addWorkItem('manage', new WorkItem('project_milestone_status_rule_index', 'Status rules'));
		}
	}
	
	public function onGroupWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		if ($workspace->getProject()->supportsModule('milestone')) {
			$workspace->addWorkItem('summary', new WorkItem('group_milestone_summary', 'Milestones'));
			$workspace->addWorkItem('data', new WorkItem('group_milestone_editor', 'Milestones'));
		}
	}
	
	public function onAreaWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		if ($workspace->getProject()->supportsModule('milestone')) {
			$workspace->addWorkItem('area', new WorkItem('area_milestone_editor', 'Milestones'));
		}
	}
}
