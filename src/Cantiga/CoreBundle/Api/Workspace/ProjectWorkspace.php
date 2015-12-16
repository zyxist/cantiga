<?php
namespace Cantiga\CoreBundle\Api\Workspace;

use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Entity\Membership\ProjectMembershipLoader;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\Metamodel\Membership;
use Symfony\Component\Routing\RouterInterface;

/**
 * Description of AdminWorkspace
 *
 * @author Tomasz Jędrzejewski
 */
class ProjectWorkspace extends Workspace
{
	/**
	 * @var ProjectMembershipLoader 
	 */
	private $projectMembershipLoader;
	
	private $project;
	
	public function __construct(ProjectMembershipLoader $pml)
	{
		$this->projectMembershipLoader = $pml;
	}

	public function getKey()
	{
		return 'project';
	}
	
	public function onWorkspaceLoaded(Membership $membership)
	{
		$this->project = $membership->getItem();
	}
	
	/**
	 * @return Project
	 */
	public function getProject()
	{
		return $this->project;
	}
	
	public function getMembershipLoader()
	{
		return $this->projectMembershipLoader;
	}

	public function getWorkspaceEvent()
	{
		return CantigaEvents::WORKSPACE_PROJECT;
	}
	
	public function getHelpPages(RouterInterface $router)
	{
		return [
			['route' => 'user_help_introduction', 'url' => $router->generate('user_help_introduction'), 'title' => 'Introduction to the system'],
			['route' => 'project_help_introduction', 'url' => $router->generate('project_help_introduction', ['slug' => $this->project->getSlug()]), 'title' => 'Introduction to projects'],
			['route' => 'project_help_members', 'url' => $router->generate('project_help_members', ['slug' => $this->project->getSlug()]), 'title' => 'Member management'],
		];
	}
}
