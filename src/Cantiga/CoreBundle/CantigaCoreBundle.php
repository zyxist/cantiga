<?php
namespace Cantiga\CoreBundle;

use Cantiga\CoreBundle\Api\AppMails;
use Cantiga\CoreBundle\Api\AppTexts;
use Cantiga\CoreBundle\Api\Modules;
use Cantiga\CoreBundle\Api\Workspaces;
use Cantiga\CoreBundle\CoreTexts;
use Cantiga\CoreBundle\DependencyInjection\ExtensionPointPass;
use Cantiga\CoreBundle\DependencyInjection\MembershipPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CantigaCoreBundle extends Bundle
{
	public function boot()
	{
		Workspaces::registerWorkspace('user', 'User workspace', 'cantiga_home_page', 'ROLE_USER', 'green');
		Workspaces::registerWorkspace('area', 'Area workspace', 'area_dashboard', 'ROLE_AREA_AWARE', 'purple', Workspaces::HIDDEN);
		Workspaces::registerWorkspace('group', 'Group workspace', 'group_dashboard', 'ROLE_GROUP_AWARE', 'black', Workspaces::HIDDEN);
		Workspaces::registerWorkspace('project', 'Project workspace', 'project_dashboard', 'ROLE_PROJECT_AWARE', 'blue', Workspaces::HIDDEN);
		Workspaces::registerWorkspace('admin', 'Admin workspace', 'admin_dashboard', 'ROLE_ADMIN', 'red');
		
		AppTexts::registerName(CoreTexts::AREA_REQUEST_CREATION_STEP1_TEXT);
		AppTexts::registerName(CoreTexts::AREA_REQUEST_CREATION_STEP2_TEXT);
		AppTexts::registerName(CoreTexts::TERMS_OF_USE_TEXT);
		AppTexts::registerName(CoreTexts::LOGIN_TEXT);
		AppTexts::registerName(CoreTexts::DASHBOARD_ADMIN_TEXT);
		AppTexts::registerName(CoreTexts::DASHBOARD_USER_TEXT);
		AppTexts::registerName(CoreTexts::DASHBOARD_PROJECT_TEXT);
		AppTexts::registerName(CoreTexts::DASHBOARD_GROUP_TEXT);
		AppTexts::registerName(CoreTexts::DASHBOARD_AREA_TEXT);

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
		$container->addCompilerPass(new MembershipPass());
	}
}
