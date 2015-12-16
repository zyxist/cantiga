<?php
namespace Cantiga\CoreBundle\Api\Workspace;

use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * Description of AdminWorkspace
 *
 * @author Tomasz Jędrzejewski
 */
class AdminWorkspace extends Workspace
{
	public function getKey()
	{
		return 'admin';
	}
	
	public function isAvailable()
	{
		return true;
	}

	public function getWorkspaceEvent()
	{
		return CantigaEvents::WORKSPACE_ADMIN;
	}
	
	public function getHelpPages(RouterInterface $router)
	{
		return [
			['route' => 'user_help_introduction', 'url' => $router->generate('user_help_introduction'), 'title' => 'Introduction to the system'],
			['route' => 'admin_help_managing', 'url' => $router->generate('admin_help_managing'), 'title' => 'Managing the system']
		];
	}
}
