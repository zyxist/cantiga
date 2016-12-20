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
namespace Cantiga\MilestoneBundle\Extension;

use Cantiga\Components\Hierarchy\MembershipStorageInterface;
use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Extension\DashboardExtensionInterface;
use Cantiga\MilestoneBundle\Repository\MilestoneStatusRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;

class DashboardMilestoneExtension implements DashboardExtensionInterface
{
	/**
	 * @var MilestoneStatusRepository
	 */
	private $repository;
	/**
	 * @var EngineInterface
	 */
	private $templating;
	/**
	 * @var MembershipStorageInterface
	 */
	private $membershipStorage;
	
	public function __construct(MilestoneStatusRepository $repository, MembershipStorageInterface $membershipStorage, EngineInterface $templating)
	{
		$this->repository = $repository;
		$this->templating = $templating;
		$this->membershipStorage = $membershipStorage;
	}
	
	public function getPriority()
	{
		return self::PRIORITY_HIGH;
	}

	public function render(CantigaController $controller, Request $request, Workspace $workspace, Project $project = null)
	{
		$place = $this->membershipStorage->getMembership()->getPlace()->getPlace();
		return $this->templating->render('CantigaMilestoneBundle:Dashboard:milestone-progress.html.twig', [
			'milestones' => $this->repository->findNearestMilestones($place, $project, time()),
			'progress' => $this->repository->computeTotalProgress($place, $project),
			'milestoneEditorPage' => lcfirst($place->getType()).'_milestone_editor',
		]);
	}
}
