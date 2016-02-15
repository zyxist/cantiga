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
namespace Cantiga\MilestoneBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\GroupPageController;
use Cantiga\MilestoneBundle\Repository\MilestoneSummaryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/group/{slug}/milestone-summary")
 * @Security("has_role('ROLE_GROUP_MEMBER')")
 */
class GroupMilestoneSummaryController extends GroupPageController
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
			->entryLink($this->trans('Milestones', [], 'pages'), 'group_milestone_summary', ['slug' => $this->getSlug()]);
	}
	
	/**
	 * @Route("/view", name="group_milestone_summary")
	 */
	public function indexAction(Request $request)
	{
		return $this->render(self::MILESTONE_TEMPLATE, array(
			'pageTitle' => 'Milestones',
			'pageSubtitle' => 'View the progress of areas and groups',
			'viewPage' => 'group_milestone_summary',
			'individualPage' => 'group_milestone_summary_individual_areas',
			'editPage' => 'group_milestone_editor',
			'areaInfoPage' => 'group_area_info',
			'showTypeSelector' => false,			
			'type' => 0,
			'items' => $this->repository->findMilestoneProgressForAreasInGroup($this->getMembership()->getItem()),
		));
	}
	
	/**
	 * @Route("/individual/areas", name="group_milestone_summary_individual_areas")
	 */
	public function individualListAction(Request $request)
	{
		list($milestones, $items) = $this->repository->findTotalAreaCompleteness($this->getMembership()->getItem());
		return $this->render(self::MILESTONE_INDIVIDUAL_TEMPLATE, array(
			'pageTitle' => 'Milestones',
			'pageSubtitle' => 'See which milestones are completed in each area',
			'viewPage' => 'group_milestone_summary',
			'individualPage' => 'group_milestone_summary_individual_areas',
			'editPage' => 'group_milestone_editor',
			'areaInfoPage' => 'group_area_info',
			'showTypeSelector' => false,
			'type' => 2,
			'milestones' => $milestones,
			'items' => $items,
		));
	}
}
