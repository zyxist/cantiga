<?php
namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\CoreTexts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/project/{slug}")
 */
class ProjectDashboardController extends ProjectPageController
{
	use DashboardTrait;
	
	/**
	 * @Route("/index", name="project_dashboard")
	 */
    public function indexAction(Request $request)
    {
        return $this->render('CantigaCoreBundle:Project:dashboard.html.twig', array(
			'user' => $this->getUser(),
			'project' => $this->getActiveProject(),
			'topExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::PROJECT_DASHBOARD_TOP)),
			'rightExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::PROJECT_DASHBOARD_RIGHT)),
			'centralExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::PROJECT_DASHBOARD_CENTRAL)),
		));
    }
	
	/**
	 * @Route("/help/introduction", name="project_help_introduction")
	 */
	public function helpIntroductionAction(Request $request)
	{
		return $this->renderHelpPage($request, 'project_help_introduction', CoreTexts::HELP_PROJECT_INTRODUCTION);
	}
	
	/**
	 * @Route("/help/members", name="project_help_members")
	 */
	public function helpMembersAction(Request $request)
	{
		return $this->renderHelpPage($request, 'project_help_members', CoreTexts::HELP_PROJECT_MEMBERS);
	}
}
