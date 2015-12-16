<?php
namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CoreBundle\CoreExtensions;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/stats/area")
 * @Security("has_role('ROLE_PROJECT_VISITOR')")
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
