<?php
namespace Cantiga\CoreBundle\EventListener;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Workspaces;
use Cantiga\CoreBundle\Api\WorkspaceSourceInterface;
use Cantiga\CoreBundle\Event\ShowBreadcrumbsEvent;
use Cantiga\CoreBundle\Event\ShowHelpEvent;
use Cantiga\CoreBundle\Event\ShowTasksEvent;
use Cantiga\CoreBundle\Event\ShowUserEvent;
use Cantiga\CoreBundle\Event\ShowWorkspacesEvent;
use Cantiga\CoreBundle\Event\WorkspaceEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Description of UiListener
 *
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
	
	public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authChecker, RouterInterface $router, WorkspaceSourceInterface $workspaceSource)
	{
		$this->tokenStorage = $tokenStorage;
		$this->workspaceSource = $workspaceSource;
		$this->authChecker = $authChecker;
		$this->router = $router;
	}
	
	public function onKernelController(FilterControllerEvent $event)
	{
		$controller = $event->getController();

		if(!is_array($controller)) {
			return;
		}

		$controllerObject = $controller[0];
		if($controllerObject instanceof CantigaController) {
			$this->controller = $controllerObject;
		}
	}

    public function showUser(ShowUserEvent $event)
	{
		if ($this->tokenStorage->getToken()->isAuthenticated()) {
			$user = $this->tokenStorage->getToken()->getUser();
			$event->setUser($user);
		}
    }
	
	public function showTasks(ShowTasksEvent $event)
	{
	}
	
	public function showHelpPages(ShowHelpEvent $event)
	{
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
	
	public function showBreadcrumbs(ShowBreadcrumbsEvent $event)
	{
		if (null !== $this->controller) {
			$event->setBreadcrumbs($this->controller->breadcrumbs()->fetch($this->workspaceSource->getWorkspace()));
		}
	}
}
