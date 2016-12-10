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

namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CoreBundle\CoreExtensions;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/stats/area")
 * @Security("is_granted('PLACE_VISITOR')")
 */
class ProjectStatsAreaController extends ProjectPageController
{

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->breadcrumbs()
			->workgroup('statistics')
			->entryLink($this->trans('Area statistics', [], 'pages'), 'project_stats_area_index', ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="project_stats_area_index")
	 */
	public function indexAction(Request $request)
	{
		$stats = $this->getExtensionPoints()->findImplementations(CoreExtensions::AREA_STATS, $this->getExtensionPointFilter());
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
		return $this->render('CantigaCoreBundle:ProjectStatsArea:index.html.twig', array('output' => $output));
	}

}
