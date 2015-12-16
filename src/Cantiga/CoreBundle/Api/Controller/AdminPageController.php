<?php
namespace Cantiga\CoreBundle\Api\Controller;

use Cantiga\CoreBundle\Api\Workspace\AdminWorkspace;
use Cantiga\CoreBundle\Api\WorkspaceAwareInterface;

/**
 * @author Tomasz Jędrzejewski
 */
class AdminPageController extends CantigaController implements WorkspaceAwareInterface
{
	private $workspace;
	
	public function createWorkspace()
	{
		return $this->workspace = new AdminWorkspace();
	}
	
	/**
	 * @return AdminWorkspace
	 */
	public function getWorkspace()
	{
		return $this->workspace;
	}

	public function createProjectList()
	{
		return [];
	}
}
