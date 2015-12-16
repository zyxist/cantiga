<?php
namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\AdminPageController;
use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointFilter;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\CoreBundle\Form\AdminUserForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/admin/user")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminUserController extends AdminPageController
{
	const REPOSITORY_NAME = 'cantiga.core.repo.admin_user';
	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->crudInfo = $this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('CantigaCoreBundle:AdminUser:')
			->setItemNameProperty('name')
			->setPageTitle('Users')
			->setPageSubtitle('Manage active user accounts')
			->setIndexPage('admin_user_index')
			->setInfoPage('admin_user_info')
			->setInsertPage('admin_user_insert')
			->setEditPage('admin_user_edit')
			->setRemovePage('admin_user_remove')
			->setRemoveQuestion('UserRemovalQuestionText');
		
		$this->breadcrumbs()
			->workgroup('access')
			->entryLink($this->trans('Users', [], 'pages'), $this->crudInfo->getIndexPage());
	}
		
	/**
	 * @Route("/index", name="admin_user_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
        return $this->render($this->crudInfo->getTemplateLocation().'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale()
		));
	}
	
	/**
	 * @Route("/ajax-list", name="admin_user_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', 'admin_user_info', ['id' => '::id'])
			->link('edit_link', 'admin_user_edit', ['id' => '::id'])
			->link('remove_link', 'admin_user_remove', ['id' => '::id']);

		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
        return new JsonResponse($routes->process($repository->listData($dataTable)));
	}
	
	/**
	 * @Route("/{id}/info", name="admin_user_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		return $action->run($this, $id, function(User $user) {
			$loaders = $this->getExtensionPoints()->findImplementations(CoreExtensions::MEMBERSHIP_LOADER, new ExtensionPointFilter());
			$places = [];
			foreach ($loaders as $loader) {
				foreach ($loader->loadProjectRepresentations($user) as $place) {
					$places[] = $place;
				}
			}
			return ['places' => $places];
		});
	}
	
	/**
	 * @Route("/{id}/edit", name="admin_user_edit")
	 */
	public function editAction($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, new AdminUserForm());
		return $action->run($this, $id, $request);
	}
	
	/**
	 * @Route("/{id}/remove", name="admin_user_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		return $action->run($this, $id, $request);
	}
}