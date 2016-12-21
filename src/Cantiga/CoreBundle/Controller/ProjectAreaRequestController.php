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

namespace Cantiga\CoreBundle\Controller;

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\QuestionHelper;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\CoreSettings;
use Cantiga\CoreBundle\CoreTexts;
use Cantiga\CoreBundle\Entity\AreaRequest;
use Cantiga\CoreBundle\Entity\Message;
use Cantiga\Metamodel\Exception\ModelException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/area-request")
 * @Security("is_granted('PLACE_MEMBER') and is_granted('MEMBEROF_PROJECT')")
 */
class ProjectAreaRequestController extends ProjectPageController
{

	const REPOSITORY_NAME = 'cantiga.core.repo.project_area_request';
	const FILTER_NAME = 'cantiga.core.filter.area_request';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->crudInfo = $this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('CantigaCoreBundle:ProjectAreaRequest:')
			->setItemNameProperty('name')
			->setPageTitle('Area requests')
			->setPageSubtitle('Explore submitted area requests')
			->setIndexPage('project_area_request_index')
			->setInfoPage('project_area_request_info')
			->setRemovePage('project_area_request_remove')
			->setCannotRemoveMessage('Cannot remove an approved or declined request \'0\'.')
			->setRemoveQuestion('Do you really want to remove \'0\' request?');
		$this->breadcrumbs()
			->workgroup('data')
			->entryLink($this->trans('Area requests', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		$this->get(self::REPOSITORY_NAME)->setActiveProject($this->getActiveProject());
	}

	/**
	 * @Route("/index", name="project_area_request_index")
	 */
	public function indexAction(Request $request)
	{
		$filter = $this->get(self::FILTER_NAME);
		$filter->setTargetProject($this->getActiveProject());
		$filterForm = $filter->createForm($this->createFormBuilder($filter));
		$filterForm->handleRequest($request);

		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
		$dataTable->filter($filter);
		return $this->render($this->crudInfo->getTemplateLocation() . 'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'ajaxListPage' => 'project_area_request_ajax_list',
			'filterForm' => $filterForm->createView(),
			'filter' => $filter
		));
	}

	/**
	 * @Route("/ajax/list", name="project_area_request_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$filter = $this->get(self::FILTER_NAME);
		$filter->setTargetProject($this->getActiveProject());
		$filterForm = $filter->createForm($this->createFormBuilder($filter));
		$filterForm->handleRequest($request);

		$routes = $this->dataRoutes()
			->link('info_link', 'project_area_request_info', ['id' => '::id', 'slug' => $this->getSlug()])
			->link('remove_link', 'project_area_request_remove', ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
		$dataTable->filter($filter);
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($this->getTranslator(), $dataTable)));
	}

	/**
	 * @Route("/{id}/info", name="project_area_request_info")
	 */
	public function infoAction($id, Request $request, Membership $membership)
	{
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug());
		$action->set('membership', $membership);
		return $action->run($this, $id, function(AreaRequest $item) use ($request) {
				$formModel = $this->extensionPointFromSettings(CoreExtensions::AREA_REQUEST_FORM, CoreSettings::AREA_REQUEST_FORM);
				return [
					'summary' => $formModel->createSummary(),
					'text' => $this->chooseText($item, $request),
					'actions' => $this->chooseActionsForState($item),
				];
			});
	}

	/**
	 * @Route("/{id}/ajax-feed", name="project_area_request_ajax_feed")
	 */
	public function ajaxFeedAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);
			return new JsonResponse($repository->getFeedback($item));
		} catch (Exception $ex) {
			return new JsonResponse(['status' => 0]);
		}
	}

	/**
	 * @Route("/{id}/ajax-post", name="project_area_request_ajax_post")
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
			return new JsonResponse($repository->getFeedback($item));
		} catch (Exception $ex) {
			return new JsonResponse(['status' => 0]);
		}
	}

	/**
	 * @Route("/{id}/remove", name="project_area_request_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}

	/**
	 * @Route("/{id}/verify", name="project_area_request_verify")
	 */
	public function verifyAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);
			$repository->startVerification($item, $this->getUser());
			return $this->showPageWithMessage('The verification has been started.', $this->crudInfo->getInfoPage(), ['id' => $item->getId(), 'slug' => $this->getSlug()]);
		} catch (ModelException $exception) {
			return $this->showPageWithError($exception->getMessage(), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		}
	}

	/**
	 * @Route("/{id}/approve", name="project_area_request_approve")
	 */
	public function approveAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);

			$question = new QuestionHelper($this->trans('Do you want to approve the request \'0\'? This operation cannot be undone.', [$item->getName()]));
			$question->onSuccess(function() use($repository, $item) {
				$repository->approve($item);
			});
			$question->respond('project_area_request_approve', ['id' => $item->getId(), 'slug' => $this->getSlug()]);
			$question->path($this->crudInfo->getInfoPage(), ['id' => $item->getId(), 'slug' => $this->getSlug()]);
			$question->title($this->trans('AreaRequest: 0', [$item->getName()]), $this->crudInfo->getPageSubtitle());
			$this->breadcrumbs()->link($item->getName(), $this->crudInfo->getInfoPage(), ['id' => $item->getId(), 'slug' => $this->getSlug()]);
			return $question->handleRequest($this, $request);
		} catch (ModelException $exception) {
			return $this->showPageWithError($exception->getMessage(), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		}
	}

	/**
	 * @Route("/{id}/revoke", name="project_area_request_revoke")
	 */
	public function revokeAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);

			$question = new QuestionHelper($this->trans('Do you really want to revoke the request \'0\'? This operation cannot be undone.', [$item->getName()]));
			$question->onSuccess(function() use($repository, $item) {
				$repository->revoke($item);
			});
			$question->respond('project_area_request_revoke', ['id' => $item->getId(), 'slug' => $this->getSlug()]);
			$question->path($this->crudInfo->getInfoPage(), ['id' => $item->getId(), 'slug' => $this->getSlug()]);
			$question->title($this->trans('AreaRequest: 0', [$item->getName()]), $this->crudInfo->getPageSubtitle());
			$this->breadcrumbs()->link($item->getName(), $this->crudInfo->getInfoPage(), ['id' => $item->getId(), 'slug' => $this->getSlug()]);
			return $question->handleRequest($this, $request);
		} catch (ModelException $exception) {
			return $this->showPageWithError($exception->getMessage(), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		}
	}

	private function chooseActionsForState(AreaRequest $ar)
	{
		switch ($ar->getStatus()) {
			case AreaRequest::STATUS_NEW:
				return [
					[ 'type' => 'primary', 'route' => 'project_area_request_verify', 'args' => ['id' => $ar->getId()], 'name' => $this->trans('Start verification')],
				];
			case AreaRequest::STATUS_VERIFICATION:
				return [
					[ 'type' => 'success', 'route' => 'project_area_request_approve', 'args' => ['id' => $ar->getId()], 'name' => $this->trans('Approve', [], 'general')],
					[ 'type' => 'danger', 'route' => 'project_area_request_revoke', 'args' => ['id' => $ar->getId()], 'name' => $this->trans('Revoke', [], 'general')],
				];
			case AreaRequest::STATUS_APPROVED:
				return [];
			case AreaRequest::STATUS_REVOKED:
				return [];
		}
	}

	private function chooseText(AreaRequest $ar, Request $request)
	{
		switch ($ar->getStatus()) {
			case AreaRequest::STATUS_NEW:
				$textType = CoreTexts::AREA_REQUEST_NEW_INFO_TEXT;
				break;
			case AreaRequest::STATUS_VERIFICATION:
				$textType = CoreTexts::AREA_REQUEST_VERIFICATION_INFO_TEXT;
				break;
			case AreaRequest::STATUS_APPROVED:
				$textType = CoreTexts::AREA_REQUEST_APPROVED_INFO_TEXT;
				break;
			case AreaRequest::STATUS_REVOKED:
				$textType = CoreTexts::AREA_REQUEST_REVOKED_INFO_TEXT;
				break;
		}
		return $this->getTextRepository()->getTextOrFalse($textType, $request, $this->getActiveProject());
	}

}
