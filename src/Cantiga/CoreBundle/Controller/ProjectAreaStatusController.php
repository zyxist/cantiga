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
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CoreBundle\Form\ProjectAreaStatusForm;
use Cantiga\CoreBundle\Entity\AreaStatus;

/**
 * @Route("/project/{slug}/area-status")
 * @Security("has_role('ROLE_PROJECT_MANAGER')")
 */
class ProjectAreaStatusController extends ProjectPageController
{
	const REPOSITORY_NAME = 'cantiga.core.repo.project_area_status';
	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setProject($this->getActiveProject());
		$this->crudInfo = $this->newCrudInfo($repository)
			->setTemplateLocation('CantigaCoreBundle:ProjectAreaStatus:')
			->setItemNameProperty('name')
			->setPageTitle('Area status')
			->setPageSubtitle('Manage available status flags of areas')
			->setIndexPage('project_area_status_index')
			->setInfoPage('project_area_status_info')
			->setInsertPage('project_area_status_insert')
			->setEditPage('project_area_status_edit')
			->setRemovePage('project_area_status_remove')
			->setRemoveQuestion('Do you really want to remove the status \'0\'?');
		
		$this->breadcrumbs()
			->workgroup('manage')
			->entryLink($this->trans('Area status', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}
		
	/**
	 * @Route("/index", name="project_area_status_index")
	 */
	public function indexAction(Request $request)
	{
		$dataTable = $this->crudInfo->getRepository()->createDataTable();
        return $this->render($this->crudInfo->getTemplateLocation().'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale()
		));
	}
	
	/**
	 * @Route("/ajax-list", name="project_area_status_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', 'project_area_status_info', ['id' => '::id', 'slug' => $this->getSlug()])
			->link('edit_link', 'project_area_status_edit', ['id' => '::id', 'slug' => $this->getSlug()])
			->link('remove_link', 'project_area_status_remove', ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->crudInfo->getRepository();
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
        return new JsonResponse($routes->process($repository->listData($dataTable)));
	}
	
	/**
	 * @Route("/{id}/info", name="project_area_status_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id);
	}
	 
	/**
	 * @Route("/insert", name="project_area_status_insert")
	 */
	public function insertAction(Request $request)
	{
		$entity = new AreaStatus();
		$entity->setProject($this->getActiveProject());
		
		$action = new InsertAction($this->crudInfo, $entity, new ProjectAreaStatusForm());
		$action->slug($this->getSlug());
		return $action->run($this, $request);
	}
	
	/**
	 * @Route("/{id}/edit", name="project_area_status_edit")
	 */
	public function editAction($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, new ProjectAreaStatusForm());
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}
	
	/**
	 * @Route("/{id}/remove", name="project_area_status_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}
}