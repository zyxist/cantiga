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
namespace Cantiga\CoreBundle\EventListener;

use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointFilter;
use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointsInterface;
use Cantiga\CoreBundle\Api\Workgroup;
use Cantiga\CoreBundle\Api\WorkItem;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Api\WorkspaceAwareInterface;
use Cantiga\CoreBundle\Api\WorkspaceSourceInterface;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\CoreBundle\Event\ShowProjectsEvent;
use Cantiga\CoreBundle\Event\WorkspaceEvent;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Membership;
use Cantiga\Metamodel\MembershipToken;
use LogicException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Tomasz Jędrzejewski
 */
class WorkspaceListener implements WorkspaceSourceInterface
{
	/**
	 * Chosen workspace - picked up by the main controller
	 * @var Workspace
	 */
	private $workspace;
	/**
	 * Controller that supports workspaces
	 * @var WorkspaceAwareInterface
	 */
	private $workspaceController;
	/**
	 * @var AuthorizationCheckerInterface
	 */
	private $authChecker;
	/**
	 * @var TokenStorageInterface
	 */
	private $tokenStorage;
	/**
	 * @var ExtensionPointsInterface
	 */
	private $extensionPoints;
	
	public function __construct(AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage, ExtensionPointsInterface $extensionPoints)
	{
		$this->authChecker = $authChecker;
		$this->tokenStorage = $tokenStorage;
		$this->extensionPoints = $extensionPoints;
	}
	
	public function onControllerSelected(FilterControllerEvent $event)
	{
		if (null !== $this->workspace) {
			return;
		}
		$ctrl = $event->getController();
		if (is_array($ctrl)) {
			$ctrl = $ctrl[0];
		}
		if ($ctrl instanceof WorkspaceAwareInterface) {
			$this->workspaceController = $ctrl;
			$this->workspace = $ctrl->createWorkspace();
			$membershipLoader = $this->workspace->getMembershipLoader();
			$membership = null;
			
			if (null !== $membershipLoader) {
				try {
					$user = $this->tokenStorage->getToken()->getUser();
					$membership = $membershipLoader->findMembership($event->getRequest()->get('slug'), $user);
					
					if (!($membership instanceof Membership)) {
						throw new LogicException('The membership loader did not return \'Membership\' instance.');
					}
					$project = $membershipLoader->findProjectForEntity($membership->getItem());
					
					$user->addRole($membership->getRole()->getAuthRole());
					User::installMembershipInformation($user, $membership);
					$ctrl->get('cantiga.project.settings')->setProject($project);
					$this->tokenStorage->setToken(new MembershipToken($this->tokenStorage->getToken(), $membership, $project));
				} catch(ItemNotFoundException $exception) {
					throw new AccessDeniedHttpException($exception->getMessage(), $exception);
				}
			} else {
				$oldToken = $this->tokenStorage->getToken();
				$this->tokenStorage->setToken(new UsernamePasswordToken($oldToken->getUser(), $oldToken->getCredentials(), $oldToken->getProviderKey(), $oldToken->getUser()->getRoles()));
				User::installMembershipInformation($this->tokenStorage->getToken()->getUser(), $membership = new Membership());
			}
			$this->workspace->onWorkspaceLoaded($membership);
		}
	}
	
	public function onAdminWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		$workspace->addWorkgroup(new Workgroup('access', 'Access settings', 'lock', 1));
		$workspace->addWorkgroup(new Workgroup('projects', 'Projects', 'lightbulb-o', 2));
		$workspace->addWorkgroup(new Workgroup('settings', 'Settings', 'wrench', 3));
		
		$workspace->addWorkItem('access', new WorkItem('admin_user_index', 'Users'));
		$workspace->addWorkItem('access', new WorkItem('admin_registration_index', 'User registrations'));
		
		$workspace->addWorkItem('projects', new WorkItem('admin_project_index', 'Projects'));
		$workspace->addWorkItem('projects', new WorkItem('admin_membership_index', 'Membership'));
		
		$workspace->addWorkItem('settings', new WorkItem('admin_language_index', 'Languages'));
		$workspace->addWorkItem('settings', new WorkItem('admin_app_text_index', 'Application texts'));
		$workspace->addWorkItem('settings', new WorkItem('admin_app_mail_index', 'Mail templates'));
	}
	
	public function onProjectWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		$project = $workspace->getProject();
		$workspace->addWorkgroup(new Workgroup('community', 'Community', 'users', 1));
		
		if ($this->authChecker->isGranted('ROLE_PROJECT_VISITOR')) {
			$workspace->addWorkgroup(new Workgroup('statistics', 'Statistics', 'bar-chart', 1));
		}
		if ($this->authChecker->isGranted('ROLE_PROJECT_MEMBER')) {
			$workspace->addWorkgroup(new Workgroup('data', 'Data', 'database', 2));
		}
		if ($this->authChecker->isGranted('ROLE_PROJECT_MANAGER')) {
			$workspace->addWorkgroup(new Workgroup('manage', 'Manage', 'wrench', 3));
		}
		
		$workspace->addWorkItem('community', new WorkItem('project_memberlist_index', 'Member list'));
		
		if ($project->getAreasAllowed()) {
			$workspace->addWorkItem('statistics', new WorkItem('project_stats_area_index', 'Area statistics'));
		}
		
		if ($project->getAreasAllowed()) {
			$workspace->addWorkItem('data', new WorkItem('project_area_request_index', 'Area requests'));
			$workspace->addWorkItem('data', new WorkItem('project_area_index', 'Areas'));
		}
		$workspace->addWorkItem('data', new WorkItem('project_area_group_index', 'Groups'));
		
		$workspace->addWorkItem('manage', new WorkItem('project_settings_index', 'Settings'));
		$workspace->addWorkItem('manage', new WorkItem('project_membership_index', 'Project members'));
		if ($project->getAreasAllowed()) {
			$workspace->addWorkItem('manage', new WorkItem('project_area_status_index', 'Area status'));
			$workspace->addWorkItem('manage', new WorkItem('project_territory_index', 'Territories'));
		}
		$workspace->addWorkItem('manage', new WorkItem('project_app_text_index', 'Application texts'));
	}
	
	public function onGroupWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		$workspace->addWorkgroup(new Workgroup('community', 'Community', 'users', 1));
		
		$workspace->addWorkItem('community', new WorkItem('group_memberlist_index', 'Member list'));
	}
	
	public function onAreaWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		$workspace->addWorkgroup(new Workgroup('community', 'Community', 'users', 1));
		$workspace->addWorkgroup(new Workgroup('summary', 'Summary', 'table', 2));
		$workspace->addWorkgroup(new Workgroup('area', 'Area', 'flag-o', 3));
		if ($this->authChecker->isGranted('ROLE_AREA_MANAGER')) {
			$workspace->addWorkgroup(new Workgroup('manage', 'Manage', 'wrench', 4));
		}
		
		$workspace->addWorkItem('community', new WorkItem('area_memberlist_index', 'Member list'));
		$workspace->addWorkItem('area', new WorkItem('area_profile_editor', 'Profile editor'));
		
		$workspace->addWorkItem('manage', new WorkItem('area_membership_index', 'Area members'));
	}
	
	public function onUserWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		$workspace->addWorkgroup(new Workgroup('profile', 'Profile', 'user', 1));
		$workspace->addWorkgroup(new Workgroup('areas', 'Areas', 'flag', 2));
		
		$workspace->addWorkItem('profile', new WorkItem('user_invitation_index', 'Invitations'));
		$workspace->addWorkItem('profile', new WorkItem('user_profile_personal_info', 'Personal information'));
		$workspace->addWorkItem('profile', new WorkItem('user_profile_settings', 'Settings'));
		$workspace->addWorkItem('profile', new WorkItem('user_profile_photo', 'Manage photo'));
		$workspace->addWorkItem('profile', new WorkItem('user_profile_change_password', 'Change password'));
		$workspace->addWorkItem('profile', new WorkItem('user_profile_change_mail', 'Change e-mail'));
		
		$workspace->addWorkItem('areas', new WorkItem('user_area_request_insert', 'Request area'));
		$workspace->addWorkItem('areas', new WorkItem('user_area_request_index', 'Your area requests'));
		$workspace->addWorkItem('areas', new WorkItem('user_place_index', 'Your places'));
	}
	
	public function onProjectList(ShowProjectsEvent $projects)
	{
		$loaders = $this->extensionPoints->findImplementations(CoreExtensions::MEMBERSHIP_LOADER, new ExtensionPointFilter());
		$user = $this->tokenStorage->getToken()->getUser();
		foreach ($loaders as $loader) {
			foreach ($loader->loadProjectRepresentations($user) as $proj) {
				$projects->addProject($proj);
			}
		}
	}
	
	public function getWorkspace()
	{
		return $this->workspace;
	}
}
