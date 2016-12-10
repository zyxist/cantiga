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
namespace Cantiga\MilestoneBundle\Controller;

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\MilestoneBundle\Repository\MilestoneSummaryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/milestone-summary")
 * @Security("is_granted('PLACE_VISITOR')")
 */
class ProjectMilestoneSummaryController extends ProjectPageController
{
	const REPOSITORY_NAME = 'cantiga.milestone.repo.summary';
	const MILESTONE_TEMPLATE = 'CantigaMilestoneBundle:MilestoneEditor:milestone-summary.html.twig';
	const MILESTONE_INDIVIDUAL_TEMPLATE = 'CantigaMilestoneBundle:MilestoneEditor:milestone-individual.html.twig';
	/**
	 * @var MilestoneSummaryRepository
	 */
	private $repository;
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->repository = $this->get(self::REPOSITORY_NAME);
		$this->breadcrumbs()
			->workgroup('summary')
			->entryLink($this->trans('Milestones', [], 'pages'), 'project_milestone_summary', ['slug' => $this->getSlug()]);
	}
	
	/**
	 * @Route("/view/{type}", name="project_milestone_summary", defaults={"type" = 0})
	 */
	public function indexAction($type, Request $request)
	{
		if ($type == 0) {
			$items = $this->repository->findMilestoneProgressForAreasInProject($this->getActiveProject());
		} else {
			$items = $this->repository->findMilestoneProgressForGroupsInProject($this->getActiveProject());
		}
		return $this->render(self::MILESTONE_TEMPLATE, array(
			'pageTitle' => 'Milestones',
			'pageSubtitle' => 'View the progress of areas and groups',
			'viewPage' => 'project_milestone_summary',
			'individualPage' => 'project_milestone_summary_individual_areas',
			'editPage' => 'project_milestone_editor',
			'areaInfoPage' => 'area_mgmt_info',
			'showTypeSelector' => true,			
			'type' => $type,
			'items' => $items,
		));
	}
	
	/**
	 * @Route("/individual/areas", name="project_milestone_summary_individual_areas")
	 */
	public function individualListAction(Request $request, Membership $membership)
	{
		list($milestones, $items) = $this->repository->findTotalAreaCompleteness($membership->getPlace());
		return $this->render(self::MILESTONE_INDIVIDUAL_TEMPLATE, array(
			'pageTitle' => 'Milestones',
			'pageSubtitle' => 'See which milestones are completed in each area',
			'viewPage' => 'project_milestone_summary',
			'individualPage' => 'project_milestone_summary_individual_areas',
			'editPage' => 'project_milestone_editor',
			'areaInfoPage' => 'area_mgmt_info',
			'showTypeSelector' => true,
			'type' => 2,
			'milestones' => $milestones,
			'items' => $items,
		));
	}
}
