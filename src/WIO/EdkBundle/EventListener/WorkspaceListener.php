<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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
namespace WIO\EdkBundle\EventListener;

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
		if ($workspace->getProject()->supportsModule('edk')) {
			$workspace->addWorkItem('statistics', new WorkItem('project_stats_route_index', 'Route statistics'));
			$workspace->addWorkItem('data', new WorkItem('project_route_index', 'Routes'));
		}
	}
	
	public function onGroupWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		if ($workspace->getProject()->supportsModule('edk')) {
			$workspace->addWorkItem('data', new WorkItem('group_route_index', 'Routes'));
		}
	}
	
	public function onAreaWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		if ($workspace->getProject()->supportsModule('edk')) {
			$workspace->addWorkItem('area', new WorkItem('area_note_index', 'WWW: area information'));
			$workspace->addWorkItem('area', new WorkItem('area_route_index', 'Routes'));
		}
	}
}
