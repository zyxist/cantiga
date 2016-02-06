<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace WIO\EdkBundle\Extension;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Controller\GroupPageController;
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Extension\AreaInformationExtensionInterface;
use Symfony\Component\HttpFoundation\Request;
use WIO\EdkBundle\Repository\EdkRouteRepository;

/**
 * @author Tomasz Jędrzejewski
 */
class AreaRouteExtension implements AreaInformationExtensionInterface
{
	private $repository;
	
	public function __construct(EdkRouteRepository $repository)
	{
		$this->repository = $repository;
	}
	
	public function getPriority()
	{
		return self::PRIORITY_MEDIUM;
	}

	public function render(CantigaController $controller, Request $request, Area $area)
	{
		if ($controller instanceof ProjectPageController) {
			$routeLink = 'project_route_info';
		} elseif ($controller instanceof GroupPageController) {
			$routeLink = 'group_route_info';
		}
		
		$routes = $this->repository->findRouteSummary($area);
		return $controller->renderView('WioEdkBundle:Extension:area-routes.html.twig', [
			'routes' => $routes,
			'routeLink' => $routeLink,
		]);
	}
}
