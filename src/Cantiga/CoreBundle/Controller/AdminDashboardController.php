<?php
namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\AdminPageController;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\CoreTexts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminDashboardController extends AdminPageController
{
	use DashboardTrait;
	
	/**
	 * @Route("/index", name="admin_dashboard")
	 */
    public function indexAction(Request $request)
    {
        return $this->render('CantigaCoreBundle:Admin:dashboard.html.twig', array(
			'user' => $this->getUser(),
			'topExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::ADMIN_DASHBOARD_TOP)),
			'rightExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::ADMIN_DASHBOARD_RIGHT)),
			'centralExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::ADMIN_DASHBOARD_CENTRAL)),
		));
    }
	
	/**
	 * @Route("/help/introduction", name="admin_help_managing")
	 */
	public function helpManagingAction(Request $request)
	{
		return $this->renderHelpPage($request, 'admin_help_managing', CoreTexts::HELP_ADMIN_MANAGING);
	}
}
