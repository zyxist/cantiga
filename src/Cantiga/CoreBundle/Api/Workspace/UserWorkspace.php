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
class UserWorkspace extends Workspace
{
	public function getKey()
	{
		return 'user';
	}
	
	public function isAvailable()
	{
		return true;
	}

	public function getWorkspaceEvent()
	{
		return CantigaEvents::WORKSPACE_USER;
	}
	
	public function getHelpPages(RouterInterface $router)
	{
		return [
			['route' => 'user_help_introduction', 'url' => $router->generate('user_help_introduction'), 'title' => 'Introduction to the system'],
			['route' => 'user_help_profile', 'url' => $router->generate('user_help_profile'), 'title' => 'User profile'],
			['route' => 'user_help_requesting_areas', 'url' => $router->generate('user_help_requesting_areas'), 'title' => 'Requesting areas'],
			['route' => 'user_help_invitations', 'url' => $router->generate('user_help_invitations'), 'title' => 'Invitations']
		];
	}
}
