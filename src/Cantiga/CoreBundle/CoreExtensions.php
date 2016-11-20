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
namespace Cantiga\CoreBundle;

/**
 * Places, where custom extensions can be run.
 *
 * @author Tomasz Jędrzejewski
 */
class CoreExtensions
{
	const MEMBERSHIP_LOADER = 'core.membership.loader';
	const AREA_REQUEST_FORM = 'core.form.area-request';
	const AREA_FORM = 'core.form.area';
	const AREA_STATS = 'core.stats.area';
	
	const MAGIC_BUTTON = 'core.magic-button';
	
	const AREA_INFORMATION = 'core.page.area.info';
	const PROJECT_GROUP_INFORMATION = 'core.page.group.info';
	
	const USER_DASHBOARD_RIGHT = 'core.dashboard.user.right';
	const USER_DASHBOARD_CENTRAL = 'core.dashboard.user.central';
	
	const AREA_DASHBOARD_TOP = 'core.dashboard.area.top';
	const AREA_DASHBOARD_CENTRAL = 'core.dashboard.area.central';
	const AREA_DASHBOARD_RIGHT = 'core.dashboard.area.right';
	
	const GROUP_DASHBOARD_TOP = 'core.dashboard.group.top';
	const GROUP_DASHBOARD_CENTRAL = 'core.dashboard.group.central';
	const GROUP_DASHBOARD_RIGHT = 'core.dashboard.group.right';
	
	const PROJECT_DASHBOARD_TOP = 'core.dashboard.project.top';
	const PROJECT_DASHBOARD_CENTRAL = 'core.dashboard.project.central';
	const PROJECT_DASHBOARD_RIGHT = 'core.dashboard.project.right';
	
	const ADMIN_DASHBOARD_TOP = 'core.dashboard.admin.top';
	const ADMIN_DASHBOARD_CENTRAL = 'core.dashboard.admin.central';
	const ADMIN_DASHBOARD_RIGHT = 'core.dashboard.admin.right';
}
