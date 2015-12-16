<?php
namespace Cantiga\CoreBundle;

/**
 * Database tables provided and used by this bundle.
 *
 * @author Tomasz Jędrzejewski
 */
class CoreTables
{
	const APP_TEXT_TBL = 'cantiga_texts';
	const AREA_REQUEST_TBL = 'cantiga_area_requests';
	const AREA_REQUEST_COMMENT_TBL = 'cantiga_area_request_comments';
	const AREA_TBL = 'cantiga_areas';
	const AREA_MEMBER_TBL = 'cantiga_area_members';
	const AREA_STATUS_TBL = 'cantiga_area_statuses';
	const GROUP_TBL = 'cantiga_groups';
	const GROUP_MEMBER_TBL = 'cantiga_group_members';
	const MAIL_TBL = 'cantiga_mail';
	const USER_TBL = 'cantiga_users';
	const CREDENTIAL_CHANGE_TBL = 'cantiga_credential_changes';
	const USER_PROFILE_TBL = 'cantiga_user_profiles';
	const USER_REGISTRATION_TBL = 'cantiga_user_registrations';
	const PASSWORD_RECOVERY_TBL = 'cantiga_password_recovery';
	const LANGUAGE_TBL = 'cantiga_languages';
	const WORKSPACE_TBL = 'cantiga_workspaces';
	const PROJECT_TBL = 'cantiga_projects';
	const PROJECT_MEMBER_TBL = 'cantiga_project_members';
	const PROJECT_SETTINGS_TBL = 'cantiga_project_settings';
	const INVITATION_TBL = 'cantiga_invitations';
	const TERRITORY_TBL = 'cantiga_territories';
	
	const STAT_ARQ_TIME_TBL = 'cantiga_stat_arq_time';
	
	const FORM_TBL = 'cantiga_forms';
	const FORM_VERSION_TBL = 'cantiga_form_versions';
}
