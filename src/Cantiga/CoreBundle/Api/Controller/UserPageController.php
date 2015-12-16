<?php
namespace Cantiga\CoreBundle\Api\Controller;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Workspace\UserWorkspace;
use Cantiga\CoreBundle\Api\WorkspaceAwareInterface;
use Cantiga\CoreBundle\Entity\Context\UserMembershipContext;


/**
 * Description of UserPageController
 *
 * @author Tomasz Jędrzejewski
 */
class UserPageController extends CantigaController implements WorkspaceAwareInterface
{
	private $workspace;
	
	public function createWorkspace()
	{
		return $this->workspace = new UserWorkspace();
	}
	
	/**
	 * @return UserWorkspace
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
