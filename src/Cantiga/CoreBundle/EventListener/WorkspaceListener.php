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
namespace Cantiga\CoreBundle\EventListener;

use Cantiga\Components\Hierarchy\MembershipFinderInterface;
use Cantiga\Components\Workspace\WorkspaceAwareInterface;
use Cantiga\CoreBundle\Api\Workgroup;
use Cantiga\CoreBundle\Api\WorkItem;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Api\WorkspaceSourceInterface;
use Cantiga\CoreBundle\Event\WorkspaceEvent;
use Cantiga\CoreBundle\Settings\ProjectSettings;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
	private $membershipFinder;
	private $tokenStorage;
	/**
	 * @var ProjectSettings 
	 */
	private $projectSettings;
	
	public function __construct(AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage, MembershipFinderInterface $membershipFinder, ProjectSettings $settings)
	{
		$this->authChecker = $authChecker;
		$this->tokenStorage = $tokenStorage;
		$this->projectSettings = $settings;
		$this->membershipFinder = $membershipFinder;
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
			$membership = $this->membershipFinder->findMembership($this->tokenStorage->getToken()->getUser(), $event->getRequest());
			$this->workspace = $ctrl->createWorkspace($membership);
			$this->workspaceController = $ctrl;
			
			if (!empty($membership)) {
				$this->projectSettings->setProject($membership->getPlace()->getRootElement());
			}
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
		
		$workspace->addWorkItem('settings', new WorkItem('admin_language_index', 'Languages'));
		$workspace->addWorkItem('settings', new WorkItem('admin_app_text_index', 'Application texts'));
		$workspace->addWorkItem('settings', new WorkItem('admin_app_mail_index', 'Mail templates'));
	}
	
	public function onProjectWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		$project = $workspace->getProject();
		$workspace->addWorkgroup(new Workgroup('community', 'Community', 'users', 1));

		if ($this->authChecker->isGranted('PLACE_VISITOR')) {
			$workspace->addWorkgroup(new Workgroup('statistics', 'Statistics', 'bar-chart', 1));
			$workspace->addWorkgroup(new Workgroup('summary', 'Summary', 'table', 2));
		}
		if ($this->authChecker->isGranted('PLACE_MEMBER')) {
			$workspace->addWorkgroup(new Workgroup('data', 'Data', 'database', 3));
		}
		if ($this->authChecker->isGranted('PLACE_MANAGER')) {
			$workspace->addWorkgroup(new Workgroup('manage', 'Manage', 'wrench', 10));
		}
		
		if ($project->getAreasAllowed()) {
			$workspace->addWorkItem('statistics', new WorkItem('project_stats_area_index', 'Area statistics'));
		}
		
		if ($project->getAreasAllowed()) {
			$workspace->addWorkItem('data', new WorkItem('project_area_request_index', 'Area requests'));
			$workspace->addWorkItem('data', new WorkItem('area_mgmt_index', 'Areas'));
		}
		$workspace->addWorkItem('data', new WorkItem('project_buttons', 'Magic buttons'));
		$workspace->addWorkItem('data', new WorkItem('group_mgmt_index', 'Groups'));
		$workspace->addWorkItem('data', new WorkItem('project_group_category_index', 'Group categories'));
		
		$workspace->addWorkItem('manage', new WorkItem('project_settings_index', 'Settings'));
		if ($project->getAreasAllowed()) {
			$workspace->addWorkItem('manage', new WorkItem('project_area_status_index', 'Area status'));
			$workspace->addWorkItem('manage', new WorkItem('project_territory_index', 'Territories'));
		}
		$workspace->addWorkItem('manage', new WorkItem('project_app_text_index', 'Application texts'));
	}
	
	public function onGroupWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		$project = $workspace->getProject();
		
		$workspace->addWorkgroup(new Workgroup('community', 'Community', 'users', 1));
		$workspace->addWorkgroup(new Workgroup('summary', 'Summary', 'table', 2));
		$workspace->addWorkgroup(new Workgroup('data', 'Data', 'database', 3));
		if ($project->getAreasAllowed()) {
			$workspace->addWorkItem('data', new WorkItem('area_mgmt_index', 'Areas'));
		}
	}
	
	public function onAreaWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		$workspace->addWorkgroup(new Workgroup('community', 'Community', 'users', 1));
		$workspace->addWorkgroup(new Workgroup('summary', 'Summary', 'table', 2));
		$workspace->addWorkgroup(new Workgroup('area', 'Area', 'flag-o', 3));
		if ($this->authChecker->isGranted('PLACE_MANAGER')) {
			$workspace->addWorkgroup(new Workgroup('manage', 'Manage', 'wrench', 10));
		}
		$workspace->addWorkItem('community', new WorkItem('area_my_group', 'My group'));
		$workspace->addWorkItem('area', new WorkItem('area_profile_editor', 'Profile editor'));
	}
	
	public function onUserWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		$workspace->addWorkgroup(new Workgroup('profile', 'Profile', 'user', 1));
		
		$workspace->addRootItem(new WorkItem('user_area_request_create', 'Request area', 'thumbs-up'));
		$workspace->addRootItem(new WorkItem('user_area_request_index', 'Your area requests', 'flag-o'));
	}

	public function onProjectList(ShowProjectsEvent $projects)
	{
		$loaders = $this->extensionPoints->findImplementations(CoreExtensions::MEMBERSHIP_LOADER, new ExtensionPointFilter());
		$token = $this->tokenStorage->getToken();
		$user = $token->getUser();
		foreach ($loaders as $loader) {
			foreach ($loader->loadProjectRepresentations($user) as $proj) {
				if ($token instanceof MembershipToken) {
					if ($token->getMembership()->getItem()->getSlug() == $proj->getSlug()) {
						$projects->setActiveProject($proj);
					}
				}
				$projects->addProject($proj);
			}
		}
	}

	public function getWorkspace()
	{
		return $this->workspace;
	}
}
