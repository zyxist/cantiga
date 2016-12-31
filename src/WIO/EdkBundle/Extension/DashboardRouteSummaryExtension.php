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

use Cantiga\Components\Hierarchy\MembershipStorageInterface;
use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Extension\DashboardExtensionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use WIO\EdkBundle\Repository\EdkRegistrationSettingsRepository;
use WIO\EdkBundle\Repository\EdkRouteRepository;

/**
 * Shows the number of routes in the given item.
 */
class DashboardRouteSummaryExtension implements DashboardExtensionInterface
{
	/**
	 * @var EdkRouteRepository
	 */
	private $repository;
	/**
	 * @var EdkRegistrationSettingsRepository 
	 */
	private $settingsRepository;
	/**
	 * @var EngineInterface
	 */
	private $templating;
	/**
	 * @var MembershipStorageInterface
	 */
	private $membershipStorage;
	
	public function __construct(EdkRouteRepository $repository, EdkRegistrationSettingsRepository $settingsRepository, EngineInterface $templating, MembershipStorageInterface $membershipStorage)
	{
		$this->repository = $repository;
		$this->settingsRepository = $settingsRepository;
		$this->templating = $templating;
		$this->membershipStorage = $membershipStorage;
	}
	
	public function getPriority()
	{
		return self::PRIORITY_HIGH + 5;
	}

	public function render(CantigaController $controller, Request $request, Workspace $workspace, Project $project = null)
	{
		$rootEntity = $this->membershipStorage->getMembership()->getPlace();
		$this->repository->setRootEntity($rootEntity);
		$this->settingsRepository->setRootEntity($rootEntity);
		return $this->templating->render('WioEdkBundle:Extension:route-summary.html.twig', [
			'routeNum' => $this->repository->countRoutes(),
			'participantNum' => $this->settingsRepository->countParticipants()
		]);
	}
}
