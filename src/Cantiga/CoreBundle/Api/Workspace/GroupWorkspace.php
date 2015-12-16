<?php
namespace Cantiga\CoreBundle\Api\Workspace;

use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Entity\Membership\GroupMembershipLoader;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\Metamodel\Membership;
use Symfony\Component\Routing\RouterInterface;

/**
 * Description of AdminWorkspace
 *
 * @author Tomasz Jędrzejewski
 */
class GroupWorkspace extends Workspace
{
	/**
	 * @var GroupMembershipLoader 
	 */
	private $groupMembershipLoader;
	private $slug;
	
	public function __construct(GroupMembershipLoader $pml)
	{
		$this->groupMembershipLoader = $pml;
	}

	public function getKey()
	{
		return 'group';
	}
	
	public function getMembershipLoader()
	{
		return $this->groupMembershipLoader;
	}

	public function getWorkspaceEvent()
	{
		return CantigaEvents::WORKSPACE_GROUP;
	}
	
	public function onWorkspaceLoaded(Membership $membership)
	{
		$this->slug = $membership->getItem()->getSlug();
	}
	
	public function getHelpPages(RouterInterface $router)
	{
		return [
			['route' => 'user_help_introduction', 'url' => $router->generate('user_help_introduction'), 'title' => 'Introduction to the system'],
			['route' => 'group_help_introduction', 'url' => $router->generate('group_help_introduction', ['slug' => $this->slug]), 'title' => 'Introduction to groups'],
			['route' => 'group_help_members', 'url' => $router->generate('group_help_members', ['slug' => $this->slug]), 'title' => 'Member management'],
		];
	}
}
