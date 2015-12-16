<?php
namespace Cantiga\CoreBundle\Api\Controller;

/**
 * @author Tomasz Jędrzejewski
 */
interface ProjectAwareControllerInterface
{
	public function getActiveProject();
}
