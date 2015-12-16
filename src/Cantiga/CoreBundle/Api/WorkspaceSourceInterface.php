<?php
namespace Cantiga\CoreBundle\Api;

use Cantiga\Metamodel\MembershipLoaderInterface;

/**
 * @author Tomasz Jędrzejewski
 */
interface WorkspaceSourceInterface
{
	/**
	 * @return Workspace
	 */
	public function getWorkspace();
}
