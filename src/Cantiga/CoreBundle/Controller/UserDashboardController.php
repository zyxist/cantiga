<?php
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
	use DashboardTrait;
	
    public function indexAction(Request $request)
    {
        return $this->render('CantigaCoreBundle:User:dashboard.html.twig', array(
			'user' => $this->getUser(),
			'invitationNum' => $this->get('cantiga.core.repo.invitation')->countInvitations($this->getUser()),
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
}
