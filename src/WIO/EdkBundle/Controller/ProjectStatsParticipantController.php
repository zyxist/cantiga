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

namespace WIO\EdkBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use WIO\EdkBundle\EdkExtensions;

/**
 * @Security("is_granted('PLACE_VISITOR')")
 */
class ProjectStatsParticipantController extends ProjectPageController
{
	/**
	 * @Route("/project/{slug}/stats/participants/index", name="project_stats_participant_index")
	 */
	public function indexAction(Request $request)
	{
		$this->breadcrumbs()
			->workgroup('statistics')
			->entryLink($this->trans('Participant statistics', [], 'pages'), 'project_stats_participant_index', ['slug' => $this->getSlug()]);


		$stats = $this->getExtensionPoints()->findImplementations(EdkExtensions::PARTICIPANT_STATS, $this->getExtensionPointFilter());
		$project = $this->getActiveProject();
		$tpl = $this->get('templating');
		$output = [];
		foreach ($stats as $stat) {
			if ($stat->collectData($project)) {
				$output[] = [
					'html' => $stat->renderPlaceholder($tpl),
					'js' => $stat->renderStatistics($tpl),
					'title' => $stat->getTitle()
				];
			}
		}
		return $this->render('WioEdkBundle:ProjectStats:participants.html.twig', array('output' => $output));
	}
	
	/**
	 * @Route("/project/{slug}/area/{id}/stats", name="project_area_stats")
	 */
	public function projectAreaStatsAction($id, Request $request)
	{
		try {
			$repo = $this->get('cantiga.core.repo.project_area');
			$repo->setActiveProject($this->getActiveProject());
			$area = $repo->getItem($id);
			
			$stats = $this->getExtensionPoints()->findImplementations(EdkExtensions::AREA_PARTICIPANT_STATS, $this->getExtensionPointFilter());
			$tpl = $this->get('templating');
			$output = [];
			foreach ($stats as $stat) {
				if ($stat->collectData($area)) {
					$output[] = [
						'html' => $stat->renderPlaceholder($tpl),
						'js' => $stat->renderStatistics($tpl),
						'title' => $stat->getTitle()
					];
				}
			}
			$this->breadcrumbs()
				->workgroup('data')
				->entryLink($this->trans('Areas', [], 'pages'), 'project_area_index', ['slug' => $this->getSlug()])
				->link($area->getName(), 'project_area_info', ['slug' => $this->getSlug(), 'id' => $area->getId()])
				->link($this->trans('Participant statistics', [], 'pages'), 'project_area_stats', ['slug' => $this->getSlug(), 'id' => $area->getId()]);
			return $this->render('WioEdkBundle:ProjectStats:participants.html.twig', array('output' => $output, 'name' => $area->getName()));
		} catch(ItemNotFoundException $exception) {
			return $this->showPageWithError($this->trans($exception->getMessage()), 'project_area_index', ['slug' => $this->getSlug()]);
		}
	}
	
	/**
	 * @Route("/project/{slug}/participant_summary", name="project_participant_summary")
	 */
	public function projectTotalDailyParticipantSummaryAction(Request $request)
	{
		$this->breadcrumbs()
			->workgroup('summary')
			->entryLink($this->trans('Participants', [], 'pages'), 'project_participant_summary', ['slug' => $this->getSlug()]);
		
		$repo = $this->get('wio.edk.repo.participant');
		$dataset = $repo->fetchMultidimensionalAreaParticipantsOverTime($this->getActiveProject());
		return $this->render('WioEdkBundle:ProjectStats:participant-summary.html.twig', [
			'areaInfoPage' => 'project_area_stats',
			'dataset' => $dataset
		]);
	}

}
