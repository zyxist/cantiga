<?php
namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\AreaPageController;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\CoreTexts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/area/{slug}")
 */
class AreaDashboardController extends AreaPageController
{
	use DashboardTrait;
	
	/**
	 * @Route("/index", name="area_dashboard")
	 */
    public function indexAction(Request $request)
    {
        return $this->render('CantigaCoreBundle:Area:dashboard.html.twig', array(
			'user' => $this->getUser(),
			'area' => $this->getMembership()->getItem(),
			'topExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::AREA_DASHBOARD_TOP)),
			'rightExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::AREA_DASHBOARD_RIGHT)),
			'centralExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::AREA_DASHBOARD_CENTRAL)),
		));
    }
	
	/**
	 * @Route("/help/introduction", name="area_help_introduction")
	 */
	public function helpIntroductionAction(Request $request)
	{
		return $this->renderHelpPage($request, 'area_help_introduction', CoreTexts::HELP_AREA_INTRODUCTION);
	}
	
	/**
	 * @Route("/help/members", name="area_help_members")
	 */
	public function helpMembersAction(Request $request)
	{
		return $this->renderHelpPage($request, 'area_help_members', CoreTexts::HELP_AREA_MEMBERS);
	}
}
