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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use WIO\EdkBundle\EdkExtensions;

/**
 * @Route("/project/{slug}/stats/routes")
 * @Security("is_granted('PLACE_VISITOR')")
 */
class ProjectStatsRouteController extends ProjectPageController
{

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->breadcrumbs()
			->workgroup('statistics')
			->entryLink($this->trans('Route statistics', [], 'pages'), 'project_stats_route_index', ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="project_stats_route_index")
	 */
	public function indexAction(Request $request)
	{
		$stats = $this->getExtensionPoints()->findImplementations(EdkExtensions::ROUTE_STATS, $this->getExtensionPointFilter());
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
		return $this->render('WioEdkBundle:ProjectStats:routes.html.twig', array('output' => $output));
	}

}
