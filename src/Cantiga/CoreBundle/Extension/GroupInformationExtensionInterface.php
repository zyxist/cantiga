<?php
namespace Cantiga\CoreBundle\Extension;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Entity\Group;
use Symfony\Component\HttpFoundation\Request;

/**
 * Used for rendering blocks on the group information pages
 * 
 * @author Tomasz Jędrzejewski
 */
interface GroupInformationExtensionInterface
{
	const PRIORITY_HIGH = 0;
	const PRIORITY_MEDIUM = 100;
	const PRIORITY_LOW = 200;
	
	public function getPriority();
	/**
	 * Renders the given group information component. The method shall return a rendered HTML code to display in
	 * the group information summary on the right side.
	 *  
	 * @param CantigaController $controller Current controller
	 * @param Request $request HTTP request
	 * @param Group $group Rendered group
	 * @return string
	 */
	public function render(CantigaController $controller, Request $request, Group $group);
}
