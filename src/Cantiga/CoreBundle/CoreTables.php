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
 * Database tables provided and used by this bundle.
 */
class CoreTables
{
	const APP_TEXT_TBL = 'cantiga_texts';
	const AREA_REQUEST_TBL = 'cantiga_area_requests';
	const AREA_REQUEST_COMMENT_TBL = 'cantiga_area_request_comments';
	const AREA_TBL = 'cantiga_areas';
	const AREA_STATUS_TBL = 'cantiga_area_statuses';
	const GROUP_TBL = 'cantiga_groups';
	const GROUP_CATEGORY_TBL = 'cantiga_group_categories';
	const MAIL_TBL = 'cantiga_mail';
	const USER_TBL = 'cantiga_users';
	const CREDENTIAL_CHANGE_TBL = 'cantiga_credential_changes';
	const USER_PROFILE_TBL = 'cantiga_user_profiles';
	const USER_REGISTRATION_TBL = 'cantiga_user_registrations';
	const PASSWORD_RECOVERY_TBL = 'cantiga_password_recovery';
	const LANGUAGE_TBL = 'cantiga_languages';
	const WORKSPACE_TBL = 'cantiga_workspaces';
	const PROJECT_TBL = 'cantiga_projects';
	const PROJECT_SETTINGS_TBL = 'cantiga_project_settings';
	const INVITATION_TBL = 'cantiga_invitations';
	const TERRITORY_TBL = 'cantiga_territories';
	const CONTACT_TBL = 'cantiga_contacts';
	const STAT_ARQ_TIME_TBL = 'cantiga_stat_arq_time';
	
	const PLACE_TBL = 'cantiga_places';
}
