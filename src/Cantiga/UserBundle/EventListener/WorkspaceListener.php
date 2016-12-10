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
namespace Cantiga\UserBundle\EventListener;

use Cantiga\CoreBundle\Api\WorkItem;
use Cantiga\CoreBundle\Event\WorkspaceEvent;

class WorkspaceListener
{
	public function onProjectWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		$workspace->addWorkItem('community', new WorkItem('memberlist_index', 'Member list'));
		$workspace->addWorkItem('manage', new WorkItem('current_membership_index', 'Project members'));
	}
	
	public function onGroupWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		$workspace->addWorkItem('community', new WorkItem('memberlist_index', 'Member list'));
		$workspace->addWorkItem('manage', new WorkItem('current_membership_index', 'Group members'));
	}
	
	public function onAreaWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		$workspace->addWorkItem('community', new WorkItem('memberlist_index', 'Member list'));
		$workspace->addWorkItem('manage', new WorkItem('current_membership_index', 'Area members'));
	}
	
	public function onUserWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		$workspace->addRootItem(new WorkItem('user_invitation_index', 'Invitations', 'handshake-o'));
		$workspace->addWorkItem('profile', new WorkItem('user_profile_contact_data', 'Contact data'));
		$workspace->addWorkItem('profile', new WorkItem('user_profile_settings', 'Settings'));
		$workspace->addWorkItem('profile', new WorkItem('user_profile_photo', 'Manage photo'));
		$workspace->addWorkItem('profile', new WorkItem('user_profile_change_password', 'Change password'));
		$workspace->addWorkItem('profile', new WorkItem('user_profile_change_mail', 'Change e-mail'));
	}
}
