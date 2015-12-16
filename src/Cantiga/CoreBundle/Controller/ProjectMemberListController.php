<?php
namespace Cantiga\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;

/**
 * @Route("/project/{slug}/memberlist")
 * @Security("has_role('ROLE_PROJECT_AWARE')")
 */
class ProjectMemberListController extends ProjectPageController
{
	const REPOSITORY_NAME = 'cantiga.core.repo.project_memberlist';
	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->crudInfo = $this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('CantigaCoreBundle:ProjectMemberList:')
			->setItemNameProperty('name')
			->setPageTitle('Member list')
			->setPageSubtitle('Explore your colleagues')
			->setIndexPage('project_memberlist_index')
			->setInfoPage('project_memberlist_profile')
			->setInfoTemplate('profile.html.twig');
		$this->breadcrumbs()
			->workgroup('community')
			->entryLink($this->trans('Member list', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}
	
	/**
	 * @Route("/index", name="project_memberlist_index")
	 */
    public function indexAction(Request $request)
    {
		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
        return $this->render('CantigaCoreBundle:ProjectMemberList:index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale()
		));
    }

	/**
	 * @Route("/{id}/profile", name="project_memberlist_profile")
	 */
	public function profileAction($id, Request $request)
	{
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug());
		$project = $this->getActiveProject();
		return $action
			->fetch(function($repository, $id) use($project) {
				return $repository->getItem($project, $id);
			})
			->run($this, $id);
	}
	
	/**
	 * @Route("/ajax/list", name="project_memberlist_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()->link('profile_link', 'project_memberlist_profile', ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
        return new JsonResponse($routes->process($repository->listData($this->getActiveProject(), $dataTable)));
	}
}
