<?php
namespace Cantiga\CoreBundle\Block;

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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;

/**
 * Displays various UI gadgets related to Cantiga functionality.
 *
 * @author Tomasz Jędrzejewski
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
	 * @var Twig_Environment
	 */
	private $tpl;
	/**
	 * @var TranslatorInterface
	 */
	private $translator;
	
	public function __construct(WorkspaceSourceInterface $wsi, EventDispatcherInterface $eventDispatcher, Twig_Environment $twig, TranslatorInterface $translator)
	{
		$this->workspaceSource = $wsi;
		$this->dispatcher = $eventDispatcher;
		$this->translator = $translator;
		$this->tpl = $twig;
	}
	
	public function showHelpAction()
	{
		if (!$this->dispatcher->hasListeners(CantigaEvents::UI_HELP)) {
			return '';
		}

		$listEvent = $this->dispatcher->dispatch(CantigaEvents::UI_HELP, new ShowHelpEvent());
		return $this->tpl->render('CantigaCoreBundle:Components:help-list.html.twig', array('pages' => $listEvent->getPages()));
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
				? $listEvent->getActiveProject()->getName()
				: $this->translator->trans($workspace['title'], [], 'pages')),
			'projects' => $listEvent->getProjects(),
			'workspaces' => $workspaceEvent->getWorkspaces()
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
			'user' => $userEvent->getUser()
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
}
