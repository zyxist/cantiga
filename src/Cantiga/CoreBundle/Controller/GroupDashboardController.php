<?php
namespace Cantiga\CoreBundle\Controller;
use Cantiga\CoreBundle\Api\Controller\GroupPageController;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\CoreTexts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("/group/{slug}")
 */
class GroupDashboardController extends GroupPageController
{
	use DashboardTrait;
	
	/**
	 * @Route("/index", name="group_dashboard")
	 */
    public function indexAction(Request $request)
    {
        return $this->render('CantigaCoreBundle:Group:dashboard.html.twig', array(
			'user' => $this->getUser(),
			'group' => $this->getMembership()->getItem(),
			'topExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::GROUP_DASHBOARD_TOP)),
			'rightExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::GROUP_DASHBOARD_RIGHT)),
			'centralExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::GROUP_DASHBOARD_CENTRAL)),
		));
    }
	
	/**
	 * @Route("/help/introduction", name="group_help_introduction")
	 */
	public function helpIntroductionAction(Request $request)
	{
		return $this->renderHelpPage($request, 'group_help_introduction', CoreTexts::HELP_GROUP_INTRODUCTION);
	}
	
	/**
	 * @Route("/help/members", name="group_help_members")
	 */
	public function helpMembersAction(Request $request)
	{
		return $this->renderHelpPage($request, 'group_help_members', CoreTexts::HELP_GROUP_MEMBERS);
	}
}
