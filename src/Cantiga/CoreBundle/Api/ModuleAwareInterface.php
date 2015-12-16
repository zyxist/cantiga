<?php
namespace Cantiga\CoreBundle\Api;

/**
 * @author Tomasz Jędrzejewski
 */
interface ModuleAwareInterface
{
	/**
	 * Returns the name of the module.
	 * 
	 * @return string
	 */
	public function getModule();
}
