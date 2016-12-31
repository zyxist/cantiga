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
namespace WIO\EdkBundle\EventListener;

use Cantiga\CoreBundle\Api\Workgroup;
use Cantiga\CoreBundle\Api\WorkItem;
use Cantiga\CoreBundle\Event\ContextMenuEvent;
use Cantiga\CoreBundle\Event\WorkspaceEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class WorkspaceListener
{
	/**
	 * @var AuthorizationCheckerInterface 
	 */
	private $authChecker;
	
	public function __construct(AuthorizationCheckerInterface $authChecker)
	{
		$this->authChecker = $authChecker;
	}
	
	public function onProjectWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		if ($workspace->getProject()->supportsModule('edk')) {
			$workspace->addWorkgroup(new Workgroup('participants', 'Participants', 'male', 6));
			
			$workspace->addWorkItem('statistics', new WorkItem('project_stats_route_index', 'Route statistics'));
			$workspace->addWorkItem('statistics', new WorkItem('project_stats_participant_index', 'Participant statistics'));
			$workspace->addWorkItem('summary', new WorkItem('project_participant_summary', 'Participants'));
			$workspace->addWorkItem('data', new WorkItem('edk_route_index', 'Routes'));
			
			$workspace->addWorkItem('participants', new WorkItem('edk_reg_settings_index', 'Registration settings'));
		}
	}
	
	public function onGroupWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		if ($workspace->getProject()->supportsModule('edk')) {
			$workspace->addWorkgroup(new Workgroup('participants', 'Participants', 'male', 4));
			
			$workspace->addWorkItem('data', new WorkItem('edk_route_index', 'Routes'));
			$workspace->addWorkItem('participants', new WorkItem('edk_reg_settings_index', 'Registration settings'));
		}
	}
	
	public function onAreaWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		
		if ($workspace->getProject()->supportsModule('edk')) {
			$workspace->addWorkgroup(new Workgroup('participants', 'Participants', 'male', 4));
			
			$workspace->addWorkItem('area', new WorkItem('area_note_index', 'WWW: area information'));
			$workspace->addWorkItem('area', new WorkItem('edk_route_index', 'Routes'));
			
			$workspace->addWorkItem('participants', new WorkItem('edk_reg_settings_index', 'Registration settings'));
			$workspace->addWorkItem('participants', new WorkItem('area_edk_message_index', 'Messages'));
			$workspace->addWorkItem('participants', new WorkItem('area_stats_participant_index', 'Participant statistics'));
			if ($this->authChecker->isGranted('PLACE_PD_ADMIN')) {
				$workspace->addWorkItem('participants', new WorkItem('area_edk_participant_index', 'Participants'));
			}
		}
	}
	
	public function onProjectAreaInfo(ContextMenuEvent $event)
	{
		$event->addLink('Participant statistics', 'project_area_stats', ['id' => $event->getEntity()->getId()]);
	}
}
