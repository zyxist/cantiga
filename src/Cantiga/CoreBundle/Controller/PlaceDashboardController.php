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

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Api\Controller\WorkspaceController;
use Cantiga\CoreBundle\Controller\Traits\DashboardTrait;
use Cantiga\CoreBundle\CoreExtensions;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/s/{slug}")
 */
class PlaceDashboardController extends WorkspaceController
{
	use DashboardTrait;

	/**
	 * @Route("/dashboard", name="place_dashboard")
	 */
	public function indexAction(Membership $membership, Request $request)
	{
		switch ($membership->getPlace()->getTypeName()) {
			case 'Project':
				$topExtensions = CoreExtensions::PROJECT_DASHBOARD_TOP;
				$rightExtensions = CoreExtensions::PROJECT_DASHBOARD_RIGHT;
				$centralExtensions = CoreExtensions::PROJECT_DASHBOARD_CENTRAL;
				break;
			case 'Group':
				$topExtensions = CoreExtensions::GROUP_DASHBOARD_TOP;
				$rightExtensions = CoreExtensions::GROUP_DASHBOARD_RIGHT;
				$centralExtensions = CoreExtensions::GROUP_DASHBOARD_CENTRAL;
				break;
			case 'Area':
				$topExtensions = CoreExtensions::AREA_DASHBOARD_TOP;
				$rightExtensions = CoreExtensions::AREA_DASHBOARD_RIGHT;
				$centralExtensions = CoreExtensions::AREA_DASHBOARD_CENTRAL;
				break;
		}
		
		return $this->render('CantigaCoreBundle:Place:dashboard.html.twig', array(
			'user' => $this->getUser(),
			'place' => $membership->getPlace(),
			'topExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions($topExtensions)),
			'rightExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions($rightExtensions)),
			'centralExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions($centralExtensions)),
		));
	}

	/**
	 * @Route("/help/{page}", name="place_help_page")
	 */
	public function helpAction($page, Request $request)
	{
		return $this->renderHelpPage($request, 'place_help_page', $page);
	}
}
