<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga contributors.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace WIO\EdkBundle\Controller;

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\Components\Hierarchy\Importer\ImporterInterface;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\QuestionHelper;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\WorkspaceController;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Message;
use Cantiga\Metamodel\Exception\ModelException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use WIO\EdkBundle\Entity\EdkRoute;
use WIO\EdkBundle\Form\EdkRouteForm;

/**
 * @Route("/s/{slug}/routes")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class RouteController extends WorkspaceController
{
	const REPOSITORY_NAME = 'wio.edk.repo.route';
	const API_LIST_PAGE = 'edk_route_api_list';
	const API_RELOAD_PAGE = 'edk_route_api_reload';
	const API_UPDATE_PAGE = 'edk_route_api_update';
	const API_FEED_PAGE = 'edk_route_api_feed';
	const API_POST_PAGE = 'edk_route_api_post';
	const APPROVE_PAGE = 'edk_route_approve';
	const REVOKE_PAGE = 'edk_route_revoke';
	const AREA_INFO_PAGE = 'area_mgmt_info';
	
	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$place = $this->get('cantiga.user.membership.storage')->getMembership()->getPlace();
		
		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setRootEntity($place);
		$this->crudInfo = $this->newCrudInfo($repository)
			->setTemplateLocation('WioEdkBundle:EdkRoute:')
			->setItemNameProperty('name')
			->setPageTitle('Routes')
			->setPageSubtitle('Manage the routes of Extreme Way of the Cross')
			->setIndexPage('edk_route_index')
			->setInfoPage('edk_route_info')
			->setInsertPage('edk_route_insert')
			->setEditPage('edk_route_edit')
			->setRemovePage('edk_route_remove')
			->setItemCreatedMessage('The route \'0\' has been created.')
			->setItemUpdatedMessage('The route \'0\' has been updated.')
			->setItemRemovedMessage('The route \'0\' has been removed.')
			->setRemoveQuestion('Do you really want to remove the route \'0\'?');

		$this->breadcrumbs()
			->workgroup($place instanceof Area ? 'area' : 'data')
			->entryLink($this->trans('Routes', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="edk_route_index")
	 */
	public function indexAction(Request $request, Membership $membership)
	{
		$dataTable = $this->crudInfo->getRepository()->createDataTable();
		return $this->render($this->crudInfo->getTemplateLocation() . 'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'insertPage' => $this->crudInfo->getInsertPage(),
			'ajaxListPage' => self::API_LIST_PAGE,
			'isArea' => $this->isArea($membership),
			'importer' => $this->getImportService(),
		));
	}
	
	/**
	 * @Route("/ajax-list", name="edk_route_api_list")
	 */
	public function apiListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', $this->crudInfo->getInfoPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('edit_link', $this->crudInfo->getEditPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('remove_link', $this->crudInfo->getRemovePage(), ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->crudInfo->getRepository();
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($dataTable)));
	}
	
	/**
	 * @Route("/{id}/info", name="edk_route_info")
	 */
	public function infoAction($id, Membership $membership)
	{
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug())
			->set('ajaxReloadPage', self::API_RELOAD_PAGE)
			->set('ajaxUpdatePage', self::API_UPDATE_PAGE)
			->set('ajaxChatFeedPage', self::API_FEED_PAGE)
			->set('ajaxChatPostPage', self::API_POST_PAGE)
			->set('areaInfoPage', self::AREA_INFO_PAGE)
			->set('user', $this->getUser())
			->set('isArea', $this->isArea($membership));
		if (!$this->isArea($membership)) {
			$action->set('approvePage', self::APPROVE_PAGE)->set('revokePage', self::REVOKE_PAGE);
		}
		return $action->run($this, $id);
	}
	 
	/**
	 * @Route("/insert", name="edk_route_insert")
	 */
	public function insertAction(Request $request, Membership $membership)
	{
		$entity = new EdkRoute();
		$action = new InsertAction($this->crudInfo, $entity, EdkRouteForm::class, ['mode' => EdkRouteForm::ADD, 'areaRepository' => $this->findAreaRepository($membership)]);
		$action->slug($this->getSlug());
		$action->set('isArea', $this->isArea($membership));
		return $action->run($this, $request);
	}
	
	/**
	 * @Route("/{id}/edit", name="edk_route_edit")
	 */
	public function editAction($id, Request $request, Membership $membership)
	{
		$action = new EditAction($this->crudInfo, EdkRouteForm::class, ['mode' => EdkRouteForm::EDIT, 'areaRepository' => $this->findAreaRepository($membership)]);
		$action->slug($this->getSlug());
		$action->set('isArea', $this->isArea($membership));
		return $action->run($this, $id, $request);
	}
	
	/**
	 * @Route("/{id}/remove", name="edk_route_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}
	
	/**
	 * @Route("/{id}/api-reload", name="edk_route_api_reload")
	 */
	public function apiReloadAction($id, Request $request)
	{
		try {
			$route = $this->crudInfo->getRepository()->getItem($id);
			return new JsonResponse(['success' => 1, 'notes' => $route->getFullNoteInformation($this->getTranslator())]);
		} catch (Exception $ex) {
			return new JsonResponse(['success' => 0]);
		}
	}
	
	/**
	 * @Route("/{id}/api-update", name="edk_route_api_update")
	 */
	public function apiUpdateAction($id, Request $request)
	{
		try {
			$i = $request->get('i');
			$c = $request->get('c');
			if (empty($c)) {
				$c = null;
			}

			$route = $this->crudInfo->getRepository()->getItem($id);
			$route->saveEditableNote($this->get('database_connection'), $i, $c);
			return new JsonResponse(['success' => 1, 'note' => $route->getFullEditableNote($this->getTranslator(), $i)]);
		} catch (Exception $ex) {
			return new JsonResponse(['success' => 0]);
		}
	}
	
	/**
	 * @Route("/{id}/api-feed", name="edk_route_api_feed")
	 */
	public function ajaxFeedAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);
			return new JsonResponse($repository->getComments($item));
		} catch (Exception $ex) {
			return new JsonResponse(['status' => 0]);
		}
	}
	
	/**
	 * @Route("/{id}/api-post", name="edk_route_api_post")
	 */
	public function ajaxPostAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);
			$message = $request->get('message');
			if (!empty($message)) {
				$item->post(new Message($this->getUser(), $message));
				$repository->update($item);
			}
			return new JsonResponse($repository->getComments($item));
		} catch (Exception $ex) {
			return new JsonResponse(['status' => 0]);
		}
	}
	
	/**
	 * @Route("/{id}/approve", name="edk_route_approve")
	 */
	public function approveAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);

			$question = new QuestionHelper($this->trans('Do you want to approve the route \'0\'?', [$item->getName()], 'edk'));
			$question->onSuccess(function() use($repository, $item) {
				$repository->approve($item);
			});
			$question->respond(self::APPROVE_PAGE, ['id' => $item->getId(), 'slug' => $this->getSlug()]);
			$question->path($this->crudInfo->getInfoPage(), ['id' => $item->getId(), 'slug' => $this->getSlug()]);
			$question->title($this->trans('EdkRoute: 0', [$item->getName()]), $this->crudInfo->getPageSubtitle());
			$this->breadcrumbs()->link($item->getName(), $this->crudInfo->getInfoPage(), ['id' => $item->getId(), 'slug' => $this->getSlug()]);
			return $question->handleRequest($this, $request);
		} catch (ModelException $exception) {
			return $this->showPageWithError($exception->getMessage(), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		}
	}
	
	/**
	 * @Route("/{id}/revoke", name="edk_route_revoke")
	 */
	public function revokeAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);

			$question = new QuestionHelper($this->trans('Do you want to revoke the route \'0\'?', [$item->getName()], 'edk'));
			$question->onSuccess(function() use($repository, $item) {
				$repository->revoke($item);
			});
			$question->respond(self::REVOKE_PAGE, ['id' => $item->getId(), 'slug' => $this->getSlug()]);
			$question->path($this->crudInfo->getInfoPage(), ['id' => $item->getId(), 'slug' => $this->getSlug()]);
			$question->title($this->trans('EdkRoute: 0', [$item->getName()]), $this->crudInfo->getPageSubtitle());
			$this->breadcrumbs()->link($item->getName(), $this->crudInfo->getInfoPage(), ['id' => $item->getId(), 'slug' => $this->getSlug()]);
			return $question->handleRequest($this, $request);
		} catch (ModelException $exception) {
			return $this->showPageWithError($exception->getMessage(), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		}
	}
	
	/**
	 * @Route("/import", name="edk_route_import")
	 * @Security("is_granted('MEMBEROF_AREA') and is_granted('PLACE_MANAGER')")
	 */
	public function importAction(Request $request, Membership $membership)
	{
		try {
			$importer = $this->getImportService();
			$repository = $this->get(self::REPOSITORY_NAME);
			if (!$importer->isImportAvailable()) {
				return $this->showPageWithError($this->trans('ImportNotPossibleText'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
			}
			$question = $importer->getImportQuestion($this->crudInfo->getPageTitle(), 'ImportRoutesQuestionText');
			$question->path($this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
			$question->respond('edk_route_import', ['slug' => $this->getSlug()]);
			$question->onSuccess(function() use ($repository, $importer) {
				$repository->importFrom($importer->getImportSource(), $importer->getImportDestination());
			});
			$this->breadcrumbs()->link($this->trans('Import', [], 'general'), 'edk_route_import', ['slug' => $this->getSlug()]);
			return $question->handleRequest($this, $request);
		} catch(ModelException $exception) {
			return $this->showPageWithError($exception->getMessage(), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		}
	}
	
	public function getImportService(): ImporterInterface
	{
		return $this->get('cantiga.importer');
	}
	
	private function isArea(Membership $membership)
	{
		return ($membership->getPlace() instanceof Area);
	}

	private function findAreaRepository(Membership $membership)
	{
		$item = $membership->getPlace();
		if (!($item instanceof Area)) {
			$repository = $this->get('cantiga.core.repo.area_mgmt');
			$repository->setParentPlace($item);
			return $repository;
		}
		return null;
	}
}
