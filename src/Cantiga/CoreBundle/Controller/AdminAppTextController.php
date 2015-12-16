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
use Cantiga\CoreBundle\Form\AppTextForm;
use Cantiga\CoreBundle\Entity\AppText;

/**
 * @Route("/admin/app/text")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminAppTextController extends AdminPageController
{
	const REPOSITORY_NAME = 'cantiga.core.repo.text';
	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->crudInfo = $this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('CantigaCoreBundle:AppText:')
			->setItemNameProperty('title')
			->setPageTitle('Application texts')
			->setPageSubtitle('Manage texts displayed in various places of the application')
			->setIndexPage('admin_app_text_index')
			->setInfoPage('admin_app_text_info')
			->setInsertPage('admin_app_text_insert')
			->setEditPage('admin_app_text_edit')
			->setRemovePage('admin_app_text_remove')
			->setItemCreatedMessage('The text \'0\' has been created.')
			->setItemUpdatedMessage('The text \'0\' has been updated.')
			->setItemRemovedMessage('The text \'0\' has been removed.')
			->setRemoveQuestion('Do you really want to remove \'0\' text?');
		
		$this->breadcrumbs()
			->workgroup('settings')
			->entryLink($this->trans('Application texts', [], 'pages'), $this->crudInfo->getIndexPage());
	}
		
	/**
	 * @Route("/index", name="admin_app_text_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
        return $this->render($this->crudInfo->getTemplateLocation().'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'insertPage' => $this->crudInfo->getInsertPage(),
			'ajaxListPage' => 'admin_app_text_ajax_list'
		));
	}
	
	/**
	 * @Route("/ajax-list", name="admin_app_text_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', $this->crudInfo->getInfoPage(), ['id' => '::id'])
			->link('edit_link', $this->crudInfo->getEditPage(), ['id' => '::id'])
			->link('remove_link', $this->crudInfo->getRemovePage(), ['id' => '::id']);

		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
        return new JsonResponse($routes->process($repository->listData($dataTable)));
	}
	
	/**
	 * @Route("/{id}/info", name="admin_app_text_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		return $action->run($this, $id);
	}
	 
	/**
	 * @Route("/insert", name="admin_app_text_insert")
	 */
	public function insertAction(Request $request)
	{
		$action = new InsertAction($this->crudInfo, new AppText(), new AppTextForm());
		return $action->run($this, $request);
	}
	
	/**
	 * @Route("/{id}/edit", name="admin_app_text_edit")
	 */
	public function editAction($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, new AppTextForm());
		return $action->run($this, $id, $request);
	}
	
	/**
	 * @Route("/{id}/remove", name="admin_app_text_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		return $action->run($this, $id, $request);
	}
}