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
namespace Cantiga\CourseBundle\Extension;

use Cantiga\Components\Hierarchy\MembershipStorageInterface;
use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Extension\DashboardExtensionInterface;
use Cantiga\CourseBundle\Repository\AreaCourseRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Shows the number of completed courses for the given area on the dashboard.
 */
class DashboardCourseCountExtension implements DashboardExtensionInterface
{
	/**
	 * @var AreaCourseRepository
	 */
	private $repository;
	/**
	 * @var MembershipStorageInterface
	 */
	private $membershipStorage;
	
	public function __construct(AreaCourseRepository $repository, MembershipStorageInterface $membershipStorage)
	{
		$this->repository = $repository;
		$this->membershipStorage = $membershipStorage;
	}
	
	public function getPriority()
	{
		return self::PRIORITY_HIGH + 5;
	}

	public function render(CantigaController $controller, Request $request, Workspace $workspace, Project $project = null)
	{
		$this->repository->setArea($this->membershipStorage->getMembership()->getPlace());
		return $controller->renderView('CantigaCourseBundle:Extension:course-summary.html.twig', ['progress' => $this->repository->findProgress()]);
	}
}
