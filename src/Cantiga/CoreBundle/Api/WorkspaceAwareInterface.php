<?php
namespace Cantiga\CoreBundle\Api;

/**
 * Implemented by controllers that work in the context of a workspace. A single controller
 * is strictly tied to the given workspace and marks it by returning its instance. This
 * instance is further configured and used in rendering.
 * 
 * @author Tomasz Jędrzejewski
 */
interface WorkspaceAwareInterface
{
	/**
	 * The method shall produce an instance of the concrete workspace.
	 * 
	 * @return Workspace
	 */
	public function createWorkspace();
}
