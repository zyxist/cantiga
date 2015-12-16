<?php
namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CoreBundle\Entity\AppText;
use Cantiga\CoreBundle\Form\AppTextForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/app/text")
 * @Security("has_role('ROLE_PROJECT_MANAGER')")
 */
class ProjectAppTextController extends ProjectPageController
{
	const REPOSITORY_NAME = 'cantiga.core.repo.text';
	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setProject($this->getActiveProject());
		
		$this->crudInfo = $this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('CantigaCoreBundle:AppText:')
			->setItemNameProperty('title')
			->setPageTitle('Application texts')
			->setPageSubtitle('Manage project-specific texts displayed in various places of the application')
			->setIndexPage('project_app_text_index')
			->setInfoPage('project_app_text_info')
			->setInsertPage('project_app_text_insert')
			->setEditPage('project_app_text_edit')
			->setRemovePage('project_app_text_remove')
			->setItemCreatedMessage('The text \'0\' has been created.')
			->setItemUpdatedMessage('The text \'0\' has been updated.')
			->setItemRemovedMessage('The text \'0\' has been removed.')
			->setRemoveQuestion('Do you really want to remove \'0\' text?');
		
		$this->breadcrumbs()
			->workgroup('manage')
			->entryLink($this->trans('Application texts', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}
		
	/**
	 * @Route("/index", name="project_app_text_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setProject($this->getActiveProject());
		$dataTable = $repository->createDataTable();
        return $this->render($this->crudInfo->getTemplateLocation().'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'insertPage' => $this->crudInfo->getInsertPage(),
			'ajaxListPage' => 'project_app_text_ajax_list'
		));
	}
	
	/**
	 * @Route("/ajax-list", name="project_app_text_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', $this->crudInfo->getInfoPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('edit_link', $this->crudInfo->getEditPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('remove_link', $this->crudInfo->getRemovePage(), ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setProject($this->getActiveProject());
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
        return new JsonResponse($routes->process($repository->listData($dataTable)));
	}
	
	/**
	 * @Route("/{id}/info", name="project_app_text_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id);
	}
	 
	/**
	 * @Route("/insert", name="project_app_text_insert")
	 */
	public function insertAction(Request $request)
	{
		$action = new InsertAction($this->crudInfo, new AppText(), new AppTextForm());
		$action->slug($this->getSlug());
		return $action->run($this, $request);
	}
	
	/**
	 * @Route("/{id}/edit", name="project_app_text_edit")
	 */
	public function editAction($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, new AppTextForm());
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}
	
	/**
	 * @Route("/{id}/remove", name="project_app_text_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}
}