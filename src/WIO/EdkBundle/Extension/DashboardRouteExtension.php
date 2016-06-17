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
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Extension\DashboardExtensionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use WIO\EdkBundle\Repository\EdkRouteRepository;

/**
 * Shows the most recently changed routes.
 *
 * @author Tomasz Jędrzejewski
 */
class DashboardRouteExtension implements DashboardExtensionInterface
{
	/**
	 * @var EdkRouteRepository
	 */
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

	public function render(CantigaController $controller, Request $request, Workspace $workspace, Project $project = null)
	{
		$item = $controller->getMembership()->getItem();
		$this->repository->setRootEntity($item);
		return $this->templating->render('WioEdkBundle:Extension:recent-routes.html.twig', ['routeInfoPath' => lcfirst($item->getEntity()->getType()).'_route_info', 'routes' => $this->repository->getRecentlyChangedRoutes(5)]);
	}
}
