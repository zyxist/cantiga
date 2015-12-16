<?php
namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CoreBundle\Entity\Territory;
use Cantiga\CoreBundle\Form\ProjectTerritoryForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/territories")
 * @Security("has_role('ROLE_PROJECT_MANAGER')")
 */
class ProjectTerritoryController extends ProjectPageController
{
	const REPOSITORY_NAME = 'cantiga.core.repo.project_territory';
	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setProject($this->getActiveProject());
		$this->crudInfo = $this->newCrudInfo($repository)
			->setTemplateLocation('CantigaCoreBundle:ProjectTerritory:')
			->setItemNameProperty('name')
			->setPageTitle('Territories')
			->setPageSubtitle('Manage the territories the areas can be assigned to')
			->setIndexPage('project_territory_index')
			->setInfoPage('project_territory_info')
			->setInsertPage('project_territory_insert')
			->setEditPage('project_territory_edit')
			->setRemovePage('project_territory_remove')
			->setItemCreatedMessage('The territory \'0\' has been created.')
			->setItemRemovedMessage('The territory \'0\' has been removed.')
			->setRemoveQuestion('Do you really want to remove the territory \'0\'?');
		
		$this->breadcrumbs()
			->workgroup('manage')
			->entryLink($this->trans('Territories', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}
		
	/**
	 * @Route("/index", name="project_territory_index")
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
	 * @Route("/ajax-list", name="project_territory_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', 'project_territory_info', ['id' => '::id', 'slug' => $this->getSlug()])
			->link('edit_link', 'project_territory_edit', ['id' => '::id', 'slug' => $this->getSlug()])
			->link('remove_link', 'project_territory_remove', ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->crudInfo->getRepository();
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
        return new JsonResponse($routes->process($repository->listData($dataTable)));
	}
	
	/**
	 * @Route("/{id}/info", name="project_territory_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id);
	}
	 
	/**
	 * @Route("/insert", name="project_territory_insert")
	 */
	public function insertAction(Request $request)
	{
		$entity = new Territory();
		$entity->setProject($this->getActiveProject());
		
		$action = new InsertAction($this->crudInfo, $entity, new ProjectTerritoryForm());
		$action->slug($this->getSlug());
		return $action->run($this, $request);
	}
	
	/**
	 * @Route("/{id}/edit", name="project_territory_edit")
	 */
	public function editAction($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, new ProjectTerritoryForm());
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}
	
	/**
	 * @Route("/{id}/remove", name="project_territory_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}
}