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

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\UserPageController;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\CoreSettings;
use Cantiga\CoreBundle\CoreTexts;
use Cantiga\CoreBundle\Entity\AreaRequest;
use Cantiga\CoreBundle\Entity\Message;
use Cantiga\CoreBundle\Form\UserAreaRequestForm;
use Cantiga\Metamodel\Exception\ModelException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/user/area/request")
 * @Security("has_role('ROLE_USER')")
 */
class UserAreaRequestController extends UserPageController
{

	const REPOSITORY_NAME = 'cantiga.core.repo.user_area_request';
	
	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	/**
	 * @var ProjectTerritoryRepository
	 */
	private $territoryRepository;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->crudInfo = $this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('CantigaCoreBundle:UserAreaRequest:')
			->setItemNameProperty('name')
			->setPageTitle('Your area requests')
			->setPageSubtitle('Inspect your requests to create a new area')
			->setIndexPage('user_area_request_index')
			->setInfoPage('user_area_request_info')
			->setInsertPage('user_area_request_insert')
			->setEditPage('user_area_request_edit')
			->setRemovePage('user_area_request_remove')
			->setCannotRemoveMessage('Cannot remove an approved or declined request \'0\'.')
			->setRemoveQuestion('Do you really want to remove \'0\' item?');

		$this->breadcrumbs()
			->workgroup('areas')
			->entryLink($this->trans('Area requests', [], 'pages'), $this->crudInfo->getIndexPage());

		$this->territoryRepository = $this->get('cantiga.core.repo.project_territory');
	}

	/**
	 * @Route("/index", name="user_area_request_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		return $this->render($this->crudInfo->getTemplateLocation().'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'requests' => $repository->findUserRequests(),
			'locale' => $request->getLocale()
		));
	}

	/**
	 * @Route("/{id}/info", name="user_area_request_info")
	 */
	public function infoAction($id, Request $request)
	{
		$action = new InfoAction($this->crudInfo);
		return $action->run($this, $id, function(AreaRequest $item) use ($request) {
				$settings = $this->getProjectSettings();
				$settings->setProject($item->getProject());
				$formModel = $this->getExtensionPoints()
					->getImplementation(CoreExtensions::AREA_REQUEST_FORM, $item->getProject()->createExtensionPointFilter()->fromSettings($settings, CoreSettings::AREA_REQUEST_FORM)
				);
				return ['summary' => $formModel->createSummary(), 'text' => $this->chooseText($item, $request)];
			});
	}

	/**
	 * @Route("/{id}/ajax-feed", name="user_area_request_ajax_feed")
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
	 * @Route("/{id}/ajax-post", name="user_area_request_ajax_post")
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
	 * @Route("/insert", name="user_area_request_insert")
	 */
	public function insertAction(Request $request)
	{
		$this->breadcrumbs()->entryLink($this->trans('Request a new area', [], 'pages'), $this->crudInfo->getInsertPage());
		$repository = $this->get(self::REPOSITORY_NAME);
		$text = $this->getTextRepository()->getText(CoreTexts::AREA_REQUEST_CREATION_STEP1_TEXT, $request);
		return $this->render($this->crudInfo->getTemplateLocation().'insert-step1.html.twig', array(
				'text' => $text,
				'availableProjects' => $repository->getAvailableProjects(),
		));
	}

	/**
	 * @Route("/insert-cont/{projectId}", name="user_area_request_insert_cont")
	 */
	public function insertContAction($projectId, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$settings = $this->getProjectSettings();
			$project = $repository->getAvailableProject($projectId);
			$settings->setProject($project);
			$formModel = $this->getExtensionPoints()
				->getImplementation(CoreExtensions::AREA_REQUEST_FORM, $project->createExtensionPointFilter()->fromSettings($settings, CoreSettings::AREA_REQUEST_FORM)
			);

			$item = new AreaRequest();
			$item->setProject($project);
			$this->territoryRepository->setProject($project);

			$form = $this->createForm(
				UserAreaRequestForm::class, $item, [
					'action' => $this->generateUrl('user_area_request_insert_cont', ['projectId' => $projectId]),
					'customFormModel' => $formModel,
					'projectSettings' => $settings,
					'territoryRepository' => $this->territoryRepository
				]
			);

			$form->handleRequest($request);

			if ($form->isValid()) {
				$id = $repository->insert($item);
				return $this->showPageWithMessage($this->trans('The area request has been created.'), 'user_area_request_info', ['id' => $id]);
			}

			$text = $this->getTextRepository()->getText(CoreTexts::AREA_REQUEST_CREATION_STEP2_TEXT, $request, $project);
			$projectSpecificText = $settings->get(CoreSettings::AREA_REQUEST_INFO_TEXT)->getValue();
			$this->breadcrumbs()->entryLink($this->trans('Request a new area', [], 'pages'), $this->crudInfo->getInsertPage());
			return $this->render($this->crudInfo->getTemplateLocation().'insert-step2.html.twig', array(
					'text' => $text,
					'projectSpecificText' => $projectSpecificText,
					'form' => $form->createView(),
					'formRenderer' => $formModel->createFormRenderer(),
					'project' => $project,
			));
		} catch (ModelException $ex) {
			return $this->showPageWithError($this->trans($ex->getMessage()), $this->crudInfo->getIndexPage());
		}
	}

	/**
	 * @Route("/{id}/edit", name="user_area_request_edit")
	 */
	public function editAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);
			$settings = $this->getProjectSettings();
			$settings->setProject($item->getProject());
			$formModel = $this->getExtensionPoints()
				->getImplementation(CoreExtensions::AREA_REQUEST_FORM, $item->getProject()->createExtensionPointFilter()->fromSettings($settings, CoreSettings::AREA_REQUEST_FORM)
			);
			$this->territoryRepository->setProject($item->getProject());

			$form = $this->createForm(
				UserAreaRequestForm::class, $item, [
					'action' => $this->generateUrl($this->crudInfo->getEditPage(), ['id' => $id]),
					'customFormModel' => $formModel,
					'projectSettings' => $settings,
					'territoryRepository' => $this->territoryRepository
				]
			);

			$form->handleRequest($request);

			if ($form->isValid()) {
				$repository->update($item);
				return $this->showPageWithMessage($this->trans('The area request has been updated.'), $this->crudInfo->getInfoPage(), ['id' => $item->getId()]);
			}
			$this->breadcrumbs()->link($item->getName(), $this->crudInfo->getInfoPage(), ['id' => $id]);
			$this->breadcrumbs()->link($this->trans('Edit', [], 'pages'), $this->crudInfo->getEditPage(), ['id' => $id]);
			return $this->render($this->crudInfo->getTemplateLocation().'edit.html.twig', array(
					'item' => $item,
					'pageTitle' => $this->crudInfo->getPageTitle(),
					'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
					'form' => $form->createView(),
					'formRenderer' => $formModel->createFormRenderer(),
					'project' => $item->getProject(),
			));
		} catch (ModelException $ex) {
			return $this->showPageWithError($this->trans($ex->getMessage()), $this->crudInfo->getIndexPage());
		}
	}

	/**
	 * @Route("/{id}/remove", name="user_area_request_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		return $action->run($this, $id, $request);
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
		return $this->getTextRepository()->getTextOrFalse($textType, $request, $ar->getProject());
	}

}
