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

use Cantiga\CoreBundle\Api\AppMails;
use Cantiga\CoreBundle\Api\AppTexts;
use Cantiga\CoreBundle\Api\Workspaces;
use Cantiga\CoreBundle\CoreTexts;
use Cantiga\CoreBundle\DependencyInjection\ExtensionPointPass;
use Cantiga\CoreBundle\DependencyInjection\ImporterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CantigaCoreBundle extends Bundle
{
	public function boot()
	{
		Workspaces::registerWorkspace('user', 'User workspace', 'cantiga_home_page', 'ROLE_USER', 'green');
		Workspaces::registerWorkspace('area', 'Area workspace', 'place_dashboard', 'MEMBEROF_AREA', 'purple', Workspaces::HIDDEN);
		Workspaces::registerWorkspace('group', 'Group workspace', 'place_dashboard', 'MEMBEROF_GROUP', 'black', Workspaces::HIDDEN);
		Workspaces::registerWorkspace('project', 'Project workspace', 'place_dashboard', 'MEMBEROF_PROJECT', 'blue', Workspaces::HIDDEN);
		Workspaces::registerWorkspace('admin', 'Admin workspace', 'admin_dashboard', 'ROLE_ADMIN', 'red');
		
		AppTexts::registerName(CoreTexts::AREA_REQUEST_CREATION_STEP1_TEXT);
		AppTexts::registerName(CoreTexts::AREA_REQUEST_NEW_INFO_TEXT);
		AppTexts::registerName(CoreTexts::AREA_REQUEST_VERIFICATION_INFO_TEXT);
		AppTexts::registerName(CoreTexts::AREA_REQUEST_APPROVED_INFO_TEXT);
		AppTexts::registerName(CoreTexts::AREA_REQUEST_REVOKED_INFO_TEXT);
		AppTexts::registerName(CoreTexts::AREA_REQUEST_CREATION_STEP2_TEXT);
		AppTexts::registerName(CoreTexts::TERMS_OF_USE_TEXT);
		AppTexts::registerName(CoreTexts::LOGIN_TEXT);
		AppTexts::registerName(CoreTexts::DASHBOARD_ADMIN_TEXT);
		AppTexts::registerName(CoreTexts::DASHBOARD_USER_TEXT);
		AppTexts::registerName(CoreTexts::DASHBOARD_PROJECT_TEXT);
		AppTexts::registerName(CoreTexts::DASHBOARD_GROUP_TEXT);
		AppTexts::registerName(CoreTexts::DASHBOARD_AREA_TEXT);
		AppTexts::registerName(CoreTexts::AREA_PROFILE_EDITOR_TEXT);

		AppTexts::registerName(CoreTexts::HELP_INTRODUCTION);
		AppTexts::registerName(CoreTexts::HELP_PROFILE);
		AppTexts::registerName(CoreTexts::HELP_REQUEST_AREAS);
		AppTexts::registerName(CoreTexts::HELP_INVITATIONS);
		AppTexts::registerName(CoreTexts::HELP_ADMIN_MANAGING);
		AppTexts::registerName(CoreTexts::HELP_PROJECT_INTRODUCTION);
		AppTexts::registerName(CoreTexts::HELP_PROJECT_MEMBERS);
		AppTexts::registerName(CoreTexts::HELP_GROUP_INTRODUCTION);
		AppTexts::registerName(CoreTexts::HELP_GROUP_MEMBERS);
		AppTexts::registerName(CoreTexts::HELP_AREA_INTRODUCTION);
		AppTexts::registerName(CoreTexts::HELP_AREA_MEMBERS);
		
		AppMails::registerName(CoreTexts::CREDENTIAL_CHANGE_MAIL);
		AppMails::registerName(CoreTexts::PASSWORD_RECOVERY_COMPLETED_MAIL);
		AppMails::registerName(CoreTexts::PASSWORD_RECOVERY_MAIL);
		AppMails::registerName(CoreTexts::USER_REGISTRATION_MAIL);
		AppMails::registerName(CoreTexts::INVITATION_MEMBER_MAIL);
		AppMails::registerName(CoreTexts::INVITATION_ANONYMOUS_MAIL);
		
		AppMails::registerName(CoreTexts::AREA_REQUEST_CREATED_MAIL);
		AppMails::registerName(CoreTexts::AREA_REQUEST_VERIFICATION_MAIL);
		AppMails::registerName(CoreTexts::AREA_REQUEST_APPROVED_MAIL);
		AppMails::registerName(CoreTexts::AREA_REQUEST_REVOKED_MAIL);
	}
	
	public function build(ContainerBuilder $container)
	{
		parent::build($container);
		$container->addCompilerPass(new ExtensionPointPass());
		$container->addCompilerPass(new ImporterPass());
	}
}
