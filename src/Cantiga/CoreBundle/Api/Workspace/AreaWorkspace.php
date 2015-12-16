<?php
namespace Cantiga\CoreBundle\Api\Workspace;

use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Entity\Membership\AreaMembershipLoader;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\CoreBundle\Exception\AreasNotSupportedException;
use Cantiga\Metamodel\Membership;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Tomasz Jędrzejewski
 */
class AreaWorkspace extends Workspace
{
	/**
	 * @var AreaMembershipLoader 
	 */
	private $areaMembershipLoader;
	/**
	 * @var string
	 */
	private $slug;
	
	public function __construct(AreaMembershipLoader $pml)
	{
		$this->areaMembershipLoader = $pml;
	}

	public function getKey()
	{
		return 'area';
	}
	
	public function getMembershipLoader()
	{
		return $this->areaMembershipLoader;
	}

	public function getWorkspaceEvent()
	{
		return CantigaEvents::WORKSPACE_AREA;
	}
	
	public function onWorkspaceLoaded(Membership $membership)
	{
		$this->slug = $membership->getItem()->getSlug();
		if (!$membership->getItem()->getProject()->getAreasAllowed()) {
			throw new AreasNotSupportedException('This project does not support areas.');
		}
	}
	
	public function getHelpPages(RouterInterface $router)
	{
		return [
			['route' => 'user_help_introduction', 'url' => $router->generate('user_help_introduction'), 'title' => 'Introduction to the system'],
			['route' => 'area_help_introduction', 'url' => $router->generate('area_help_introduction', ['slug' => $this->slug]), 'title' => 'Introduction to areas'],
			['route' => 'area_help_members', 'url' => $router->generate('area_help_members', ['slug' => $this->slug]), 'title' => 'Member management'],
		];
	}
}
