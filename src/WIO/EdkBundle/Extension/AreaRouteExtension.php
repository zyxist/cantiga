<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga contributors.
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
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Extension\AreaInformationExtensionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use WIO\EdkBundle\Repository\EdkRouteRepository;

class AreaRouteExtension implements AreaInformationExtensionInterface
{
	private $repository;
	/**
	 * @var EngineInterface
	 */
	private $templating;
	
	public function __construct(EdkRouteRepository $repository, EngineInterface $templating)
	{
		$this->repository = $repository;
		$this->templating = $templating;
	}
	
	public function getPriority()
	{
		return self::PRIORITY_MEDIUM;
	}

	public function render(CantigaController $controller, Request $request, Area $area)
	{
		$routes = $this->repository->findRouteSummary($area);
		return $this->templating->render('WioEdkBundle:Extension:area-routes.html.twig', [
			'routes' => $routes,
			'routeLink' => 'edk_route_info',
		]);
	}
}
