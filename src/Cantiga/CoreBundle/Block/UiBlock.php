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
namespace Cantiga\CoreBundle\Block;

use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Api\Workspaces;
use Cantiga\CoreBundle\Api\WorkspaceSourceInterface;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\CoreBundle\Event\ShowBreadcrumbsEvent;
use Cantiga\CoreBundle\Event\ShowHelpEvent;
use Cantiga\CoreBundle\Event\ShowProjectsEvent;
use Cantiga\CoreBundle\Event\ShowTasksEvent;
use Cantiga\CoreBundle\Event\ShowUserEvent;
use Cantiga\CoreBundle\Event\ShowWorkspacesEvent;
use Cantiga\CoreBundle\Event\WorkspaceEvent;
use Cantiga\Metamodel\MembershipToken;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;

/**
 * Displays various UI gadgets related to Cantiga functionality.
 */
class UiBlock
{
	/**
	 * @var WorkspaceSourceInterface
	 */
	private $workspaceSource;
	/**
	 * @var EventDispatcherInterface
	 */
	private $dispatcher;
	/**
	 * @var TokenStorageInterface
	 */
	private $tokenStorage;
	/**
	 * @var Twig_Environment
	 */
	private $tpl;
	/**
	 * @var TranslatorInterface
	 */
	private $translator;
	
	public function __construct(WorkspaceSourceInterface $wsi, EventDispatcherInterface $eventDispatcher, Twig_Environment $twig, TokenStorageInterface $tokenStorage, TranslatorInterface $translator)
	{
		$this->workspaceSource = $wsi;
		$this->dispatcher = $eventDispatcher;
		$this->translator = $translator;
		$this->tokenStorage = $tokenStorage;
		$this->tpl = $twig;
	}
	
	public function showHelpAction()
	{
		if (!$this->dispatcher->hasListeners(CantigaEvents::UI_HELP)) {
			return '';
		}

		$listEvent = $this->dispatcher->dispatch(CantigaEvents::UI_HELP, new ShowHelpEvent());
		return $this->tpl->render('CantigaCoreBundle:Components:help-list.html.twig', array('pages' => $listEvent->getPages(), 'route' => $listEvent->getRoute()));
	}
	
	public function showProjectsAction()
	{
		if (!$this->dispatcher->hasListeners(CantigaEvents::UI_PROJECTS) && !$this->dispatcher->hasListeners(CantigaEvents::UI_WORKSPACES)) {
			return '';
		}
		
		$workspaceEvent = $this->dispatcher->dispatch(CantigaEvents::UI_WORKSPACES, new ShowWorkspacesEvent());
		$listEvent = $this->dispatcher->dispatch(CantigaEvents::UI_PROJECTS, new ShowProjectsEvent());
		$workspace = $workspaceEvent->getActive();
		return $this->tpl->render(
			'CantigaCoreBundle:Components:project-list.html.twig', array(
			'hasProjects' => $listEvent->hasProjects(),
			'active' => ($listEvent->hasActiveProject()
				? $this->translator->trans($listEvent->getActiveProject()->getType().'Nominative: 0', [$listEvent->getActiveProject()->getName()])
				: $this->translator->trans($workspace['title'], [], 'pages')),
			'projects' => $listEvent->getProjects(),
			'workspaces' => $workspaceEvent->getWorkspaces(),
			'showArchives' => $listEvent->getShowArchives()
		));
	}
	
	public function showUserAction()
	{
		if (!$this->dispatcher->hasListeners(CantigaEvents::UI_USER)) {
			return '';
		}

		$userEvent = $this->dispatcher->dispatch(CantigaEvents::UI_USER, new ShowUserEvent());
		return $this->tpl->render(
			'CantigaCoreBundle:Components:user-info.html.twig', array(
			'user' => $userEvent->getUser(),
			'membership' => $userEvent->getMembership()
		));
	}
	
	public function showTasksAction()
	{
		if (!$this->dispatcher->hasListeners(CantigaEvents::UI_TASKS)) {
			return '';
		}

		$listEvent = $this->dispatcher->dispatch(CantigaEvents::UI_TASKS, new ShowTasksEvent());
		if (!$listEvent->hasTasks()) {
			return '';
		}
		return $this->tpl->render(
			'CantigaCoreBundle:Components:task-list.html.twig', array(
			'tasks' => $listEvent->getTasks()
		));
	}
	
	public function showMenuAction()
	{
		$workspace = $this->workspaceSource->getWorkspace();
		if (null === $workspace) {
			return '';
		}
		$event = new WorkspaceEvent($workspace);
		$this->dispatcher->dispatch($workspace->getWorkspaceEvent(), $event);
		$this->dispatcher->dispatch(CantigaEvents::WORKSPACE_GENERAL, $event);
		return $this->tpl->render(
			'CantigaCoreBundle:Components:workspace-menu.html.twig', array(
			'workspace' => $workspace,
			'workspaceInfo' => Workspaces::get($workspace->getKey()),
			'currentWorkgroup' => $event->getCurrentWorkgroup(),
			'currentPage' => $event->getCurrentPage(),
			'workspaceName' => $this->createFullWorkspaceName($workspace),
		));
	}
	
	public function showBreadcrumbsAction()
	{
		$event = $this->dispatcher->dispatch(CantigaEvents::UI_BREADCRUMBS, new ShowBreadcrumbsEvent());
		return $this->tpl->render(
			'CantigaCoreBundle:Components:breadcrumbs.html.twig', array(
			'hasBreadcrumbs' => $event->hasBreadcrumbs(),
			'breadcrumbs' => $event->getBreadcrumbs()
		));
	}
	
	private function createFullWorkspaceName(Workspace $workspace): string
	{
		$token = $this->tokenStorage->getToken();
		if ($token instanceof MembershipToken) {
			$entity = $token->getMembership()->getItem();
			return $this->translator->trans($entity->getTypeName().'Nominative: 0', [$entity->getName()]);
		} else {
			return $this->translator->trans(Workspaces::get($workspace->getKey())['title'], [], 'pages');
		}
	}
}
