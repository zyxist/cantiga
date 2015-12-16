<?php
namespace Cantiga\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Controller\AdminPageController;
use Cantiga\CoreBundle\Form\AdminProjectForm;
use Cantiga\CoreBundle\Entity\Project;

/**
 * @Route("/admin/project")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminProjectController extends AdminPageController
{
	const REPOSITORY_NAME = 'cantiga.core.repo.project';
	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->crudInfo = $this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('CantigaCoreBundle:AdminProject:')
			->setItemNameProperty('name')
			->setPageTitle('Projects')
			->setPageSubtitle('Manage your active projects')
			->setIndexPage('admin_project_index')
			->setInfoPage('admin_project_info')
			->setInsertPage('admin_project_insert')
			->setEditPage('admin_project_edit')
			->setRemovePage('admin_project_remove')
			->setRemoveQuestion('Do you really want to remove \'0\' item?');
		
		$this->breadcrumbs()
			->workgroup('projects')
			->entryLink($this->trans('Projects', [], 'pages'), $this->crudInfo->getIndexPage());
	}
		
	/**
	 * @Route("/index", name="admin_project_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
        return $this->render('CantigaCoreBundle:AdminProject:index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale()
		));
	}
	
	/**
	 * @Route("/ajax-list", name="admin_project_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', 'admin_project_info', ['id' => '::id'])
			->link('edit_link', 'admin_project_edit', ['id' => '::id'])
			->link('remove_link', 'admin_project_remove', ['id' => '::id']);

		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
        return new JsonResponse($routes->process($repository->listData($dataTable)));
	}
	
	/**
	 * @Route("/{id}/info", name="admin_project_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		return $action->run($this, $id);
	}
	 
	/**
	 * @Route("/insert", name="admin_project_insert")
	 */
	public function insertAction(Request $request)
	{
		$action = new InsertAction($this->crudInfo, new Project(), new AdminProjectForm($this->get('cantiga.core.repo.archived_project')));
		return $action->run($this, $request);
	}
	
	/**
	 * @Route("/{id}/edit", name="admin_project_edit")
	 */
	public function editAction($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, new AdminProjectForm($this->get('cantiga.core.repo.archived_project')));
		return $action->run($this, $id, $request);
	}
	
	/**
	 * @Route("/{id}/remove", name="admin_project_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		return $action->run($this, $id, $request);
	}
}