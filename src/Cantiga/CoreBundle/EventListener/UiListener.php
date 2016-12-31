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

use Cantiga\Components\Hierarchy\MembershipStorageInterface;
use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Workspaces;
use Cantiga\CoreBundle\Api\WorkspaceSourceInterface;
use Cantiga\CoreBundle\Event\ShowBreadcrumbsEvent;
use Cantiga\CoreBundle\Event\ShowHelpEvent;
use Cantiga\CoreBundle\Event\ShowProjectsEvent;
use Cantiga\CoreBundle\Event\ShowTasksEvent;
use Cantiga\CoreBundle\Event\ShowUserEvent;
use Cantiga\CoreBundle\Event\ShowWorkspacesEvent;
use Cantiga\CoreBundle\Event\WorkspaceEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Tomasz Jędrzejewski
 */
class UiListener
{

	/**
	 * @var TokenStorageInterface
	 */
	private $tokenStorage;

	/**
	 * @var AuthorizationCheckerInterface
	 */
	private $authChecker;

	/**
	 * @var RouterInterface
	 */
	private $router;

	/**
	 * Main controller, if detected.
	 * @var CantigaController
	 */
	private $controller;

	/**
	 * @var WorkspaceSourceInterface 
	 */
	private $workspaceSource;
	/**
	 * @var MembershipStorageInterface
	 */
	private $membershipStorage;

	public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authChecker, RouterInterface $router, WorkspaceSourceInterface $workspaceSource, MembershipStorageInterface $membershipStorage)
	{
		$this->tokenStorage = $tokenStorage;
		$this->workspaceSource = $workspaceSource;
		$this->authChecker = $authChecker;
		$this->membershipStorage = $membershipStorage;
		$this->router = $router;
	}

	public function onKernelController(FilterControllerEvent $event)
	{
		$controller = $event->getController();

		if (!is_array($controller)) {
			return;
		}

		$controllerObject = $controller[0];
		if ($controllerObject instanceof CantigaController) {
			$this->controller = $controllerObject;
		}
	}

	public function showUser(ShowUserEvent $event)
	{
		if ($this->tokenStorage->getToken()->isAuthenticated()) {
			$user = $this->tokenStorage->getToken()->getUser();
			$event->setUser($user);
		}
		if ($this->membershipStorage->hasMembership()) {
			$event->setMembership($this->membershipStorage->getMembership());
		}
	}

	public function showTasks(ShowTasksEvent $event)
	{
		
	}

	public function showHelpPages(ShowHelpEvent $event)
	{
		$event->setRoute($this->workspaceSource->getWorkspace()->getHelpRoute());
		$event->setPages($this->workspaceSource->getWorkspace()->getHelpPages($this->router));
	}

	public function showWorkspaces(ShowWorkspacesEvent $event)
	{
		$event->setWorkspaces(Workspaces::fetchByRole($this->authChecker));
		$event->setActive(Workspaces::get($this->workspaceSource->getWorkspace()->getKey()));
	}

	public function showMenu(WorkspaceEvent $event)
	{
		if (null !== $this->controller) {
			$event->setCurrentWorkgroup($this->controller->breadcrumbs()->getWorkgroup());
			$event->setCurrentPage($this->controller->breadcrumbs()->getEntryPage());
		}
	}
	
	public function showPlaces(ShowProjectsEvent $projects)
	{
		foreach ($this->membershipStorage->getPlaces() as $place) {
			if ($this->membershipStorage->hasMembership()) {
				if ($place->getSlug() == $this->membershipStorage->getMembership()->getPlace()->getSlug()) {
					$projects->setActiveProject($place);
				}
				if ($place->getArchived()) {
					$projects->setShowArchives(true);
				}
			}
			$projects->addProject($place);
		}
	}

	public function showBreadcrumbs(ShowBreadcrumbsEvent $event)
	{
		if (null !== $this->controller) {
			$event->setBreadcrumbs($this->controller->breadcrumbs()->fetch($this->workspaceSource->getWorkspace()));
		}
	}

}
