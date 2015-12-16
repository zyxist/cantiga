<?php
namespace WIO\EdkBundle\Extension;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Extension\AreaInformationExtensionInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of AreaInformationMapExtension
 *
 * @author Tomasz Jędrzejewski
 */
class AreaInformationMapExtension implements AreaInformationExtensionInterface
{
	public function getPriority()
	{
		return self::PRIORITY_HIGH;
	}

	public function render(CantigaController $controller, Request $request, Area $area)
	{
		$data = $area->getCustomData();
		if (!empty($data['positionLat']) && !empty($data['positionLng'])) {
			return $controller->renderView('WioEdkBundle:Extension:area-information-map.html.twig', [
				'positionLat' => $data['positionLat'],
				'positionLng' => $data['positionLng']
			]);
		}
		return '';
	}
}
