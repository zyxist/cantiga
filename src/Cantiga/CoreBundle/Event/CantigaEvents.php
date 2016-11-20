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
namespace Cantiga\CoreBundle\Event;

/**
 * @author Tomasz Jędrzejewski
 */
class CantigaEvents
{
	/**
	 * Call for populating the workspace - all workspaces captured
	 */
	const WORKSPACE_GENERAL = 'cantiga.workspace.general';
	/**
	 * Call for populating the admin workspace
	 */
	const WORKSPACE_ADMIN = 'cantiga.workspace.admin';
	/**
	 * Call for populating the project workspace
	 */
	const WORKSPACE_PROJECT = 'cantiga.workspace.project';
	/**
	 * Call for populating the group workspace
	 */
	const WORKSPACE_GROUP = 'cantiga.workspace.group';
	/**
	 * Call for populating the area workspace
	 */
	const WORKSPACE_AREA = 'cantiga.workspace.area';
	/**
	 * Call for populating the user workspace
	 */
	const WORKSPACE_USER = 'cantiga.workspace.user';
	/**
	 * Populates the list of workspaces.
	 */
	const UI_WORKSPACES = 'cantiga.ui.workspaces';
	/**
	 * Used for receiving the current user by the UI.
	 */
	const UI_USER = 'cantiga.ui.user';
	/**
	 * Used for receiving project list data by the UI.
	 */
	const UI_PROJECTS = 'cantiga.ui.projects';

	/**
	 * Used for receiving notification list data by the UI.
	 */
	const UI_NOTIFICATIONS = 'cantiga.ui.notifications';

	/**
	 * Used for receiving task list data by the UI.
	 */
	const UI_TASKS = 'cantiga.ui.tasks';

	/**
	 * Used for receiving message list data by the UI.
	 */
	const UI_MESSAGES = 'cantiga.ui.messages';
	/**
	 * Populates the list of help pages.
	 */
	const UI_HELP = 'cantiga.ui.help';
	/**
	 * Populates the breadcrumbs.
	 */
	const UI_BREADCRUMBS = 'cantiga.ui.breadcrumbs';
	/**
	 * User registration attempt has happened.
	 */
	const USER_REGISTRATION = 'cantiga.user.registration';
	/**
	 * User has been activated.
	 */
	const USER_ACTIVATED = 'cantiga.user.activated';
	/**
	 * User has been removed - note that this is done by setting a flag in the database and deleting the personal information.
	 * We do not want to remove the content, so we must preserve some kind of a stub.
	 */
	const USER_REMOVED = 'cantiga.user.removed';
	/**
	 * Password recovery request.
	 */
	const USER_PASSWORD_RECOVERY = 'cantiga.user.password-recovery';
	/**
	 * Notification that the password recovery was successful.
	 */
	const USER_PASSWORD_RECOVERY_COMPLETED = 'cantiga.user.password-recovery-completed';
	/**
	 * Notification that the user has requested changing his password or e-mail from the profile.
	 */
	const USER_CREDENTIAL_CHANGE = 'cantiga.user.credential-change';
	/**
	 * Notification that the project is getting created.
	 */
	const PROJECT_CREATED = 'cantiga.project.created';
	/**
	 * Notification that the project is getting archivized.
	 */
	const PROJECT_ARCHIVIZED = 'cantiga.project.archivized';
	/**
	 * Notification that the area request has been created.
	 */
	const AREA_REQUEST_CREATED = 'cantiga.area-request.created';
	/**
	 * Notification that the area request has been created.
	 */
	const AREA_REQUEST_UPDATED = 'cantiga.area-request.updated';
	/**
	 * Notification that the area has been put into the 'verification' state.
	 */
	const AREA_REQUEST_VERIFICATION = 'cantiga.area-request.verification';
	/**
	 * Notification that the area request has been approved.
	 */
	const AREA_REQUEST_APPROVED = 'cantiga.area-request.approved';
	/**
	 * Notification that the area request has been revoked.
	 */
	const AREA_REQUEST_REVOKED = 'cantiga.area-request.revoked';
	/**
	 * Notification that a new area has been created.
	 */
	const AREA_CREATED = 'cantiga.area.created';
	/**
	 * Notification that an existing area has been updated.
	 */
	const AREA_UPDATED = 'cantiga.area.updated';
	/**
	 * Notification about a new invitation.
	 */
	const INVITATION_CREATED = 'cantiga.invitation.created';
	/**
	 * Notification that a new group has been created.
	 */
	const GROUP_CREATED = 'cantiga.group.created';
	/**
	 * Notification that an existing group has been updated.
	 */
	const GROUP_UPDATED = 'cantiga.group.updated';
	/**
	 * Notification that an existing group has been removed.
	 */
	const GROUP_REMOVED = 'cantiga.group.removed';
	/**
	 * Context menu in the project area info page - allows adding new links to it.
	 */
	const UI_CTXMENU_PROJECT_AREA = 'cantiga.ui.ctxmenu.project.area';
	/**
	 * Context menu in the group area info page - allows adding new links to it.
	 */
	const UI_CTXMENU_GROUP_AREA = 'cantiga.ui.ctxmenu.group.area';
}
