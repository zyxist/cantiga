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

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Api\Controller\AreaPageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use WIO\EdkBundle\EdkExtensions;

/**
 * @Route("/area/{slug}/stats/participants")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class AreaStatsParticipantController extends AreaPageController
{

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->breadcrumbs()
			->workgroup('participants')
			->entryLink($this->trans('Participant statistics', [], 'pages'), 'area_stats_participant_index', ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="area_stats_participant_index")
	 */
	public function indexAction(Request $request, Membership $membership)
	{
		$stats = $this->getExtensionPoints()->findImplementations(EdkExtensions::AREA_PARTICIPANT_STATS, $this->getExtensionPointFilter());
		$area = $membership->getPlace();
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
		return $this->render('WioEdkBundle:ProjectStats:participants.html.twig', array('output' => $output));
	}
}
