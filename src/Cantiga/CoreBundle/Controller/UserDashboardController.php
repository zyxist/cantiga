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

use Cantiga\CoreBundle\Api\Controller\UserPageController;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\CoreTexts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/user")
 * @Security("has_role('ROLE_USER')")
 */
class UserDashboardController extends UserPageController
{

	use Traits\DashboardTrait;

	public function indexAction(Request $request)
	{
		$repository = $this->get('cantiga.core.repo.user_dashboard');
		
		$memberEntities = $repository->getUserMembership($this->getUser());
		$areaRegistrations = $repository->getOpenAreaRegistrations();
		$invitations = $repository->getInvitations($this->getUser());
		$areaRequests = $repository->getUserAreaRequests($this->getUser());
		
		$dashboardType = $this->chooseDashboard($memberEntities, $areaRegistrations, $invitations, $areaRequests);
		
		return $this->render('CantigaCoreBundle:User:dashboard.html.twig', array(
			'user' => $this->getUser(),
			'dashboardType' => $dashboardType,
			'dashboardVars' => [
				'memberEntities' => $memberEntities,
				'areaRegistrations' => $areaRegistrations,
				'invitations' => $invitations,
				'areaRequests' => $areaRequests,
				'areaRegistrationNum' => sizeof($areaRegistrations),
				'invitationNum' => sizeof($invitations),
				'areaRequestNum' => sizeof($areaRequests)
			],
			'rightExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::USER_DASHBOARD_RIGHT)),
			'centralExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::USER_DASHBOARD_CENTRAL)),
		));
	}

	/**
	 * @Route("/help/introduction", name="user_help_introduction")
	 */
	public function helpIntroductionAction(Request $request)
	{
		return $this->renderHelpPage($request, 'user_help_introduction', CoreTexts::HELP_INTRODUCTION);
	}

	/**
	 * @Route("/help/profile", name="user_help_profile")
	 */
	public function helpProfileAction(Request $request)
	{
		return $this->renderHelpPage($request, 'user_help_profile', CoreTexts::HELP_PROFILE);
	}

	/**
	 * @Route("/help/requesting-areas", name="user_help_requesting_areas")
	 */
	public function helpRequestingAreasAction(Request $request)
	{
		return $this->renderHelpPage($request, 'user_help_requesting_areas', CoreTexts::HELP_REQUEST_AREAS);
	}

	/**
	 * @Route("/help/invitations", name="user_help_invitations")
	 */
	public function helpInvitationsAction(Request $request)
	{
		return $this->renderHelpPage($request, 'user_help_invitations', CoreTexts::HELP_INVITATIONS);
	}

	private function chooseDashboard(array $memberEntities, array $areaRegistrations, array $invitations, array $areaRequests): string
	{
		if (sizeof($memberEntities) > 0) {
			return 'CantigaCoreBundle:User:dashboard-member.html.twig';
		} elseif (sizeof($areaRegistrations) > 0 || sizeof($invitations) > 0 || sizeof($areaRequests) > 0) {
			return 'CantigaCoreBundle:User:dashboard-not-member.html.twig';
		} else {
			return 'CantigaCoreBundle:User:dashboard-nothing.html.twig';
		}
	}
}
