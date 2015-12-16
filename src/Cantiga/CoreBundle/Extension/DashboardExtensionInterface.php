<?php
namespace Cantiga\CoreBundle\Extension;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Entity\Project;
use Symfony\Component\HttpFoundation\Request;

/**
 * Used for rendering blocks on the dashboards
 * 
 * @author Tomasz Jędrzejewski
 */
interface DashboardExtensionInterface
{
	const PRIORITY_HIGH = 0;
	const PRIORITY_MEDIUM = 100;
	const PRIORITY_LOW = 200;
	
	public function getPriority();
	/**
	 * Renders the given dashboard component. The renderer has an access to the information about the currently loaded
	 * workspace and project. The controller instance can be used to access the necessary services.
	 *  
	 * @param CantigaController $controller Current controller
	 * @param Request $request HTTP request
	 * @param \Cantiga\CoreBundle\Extension\Workspace $workspace Loaded workspace
	 * @param \Cantiga\CoreBundle\Extension\Project $project Loaded project (if the controller is project-aware)
	 */
	public function render(CantigaController $controller, Request $request, Workspace $workspace, Project $project = null);
}
