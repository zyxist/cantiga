<?php
namespace Cantiga\CoreBundle\Extension;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Entity\Area;
use Symfony\Component\HttpFoundation\Request;

/**
 * Used for rendering blocks on the dashboards
 * 
 * @author Tomasz Jędrzejewski
 */
interface AreaInformationExtensionInterface
{
	const PRIORITY_HIGH = 0;
	const PRIORITY_MEDIUM = 100;
	const PRIORITY_LOW = 200;
	
	public function getPriority();
	/**
	 * Renders the given area information component. The method shall return a rendered HTML code to display in
	 * the area information summary on the right side.
	 *  
	 * @param CantigaController $controller Current controller
	 * @param Request $request HTTP request
	 * @param Area $area Rendered area
	 * @return string
	 */
	public function render(CantigaController $controller, Request $request, Area $area);
}
