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
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\AdminPageController;
use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointFilter;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\CoreBundle\Form\AdminUserForm;
use Cantiga\CoreBundle\Form\UserJumpForm;
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
		$jumpForm = $this->createForm(UserJumpForm::class, [], ['action' => $this->generateUrl($this->crudInfo->getIndexPage())]);
		$jumpForm->handleRequest($request);
		if ($jumpForm->isValid()) {
			$data = $jumpForm->getData();
			$userId = $repository->tryJumpToUser($data['login'], $data['email']);
			if (!empty($userId)) {
				return $this->redirect($this->generateUrl($this->crudInfo->getInfoPage(), ['id' => $userId]));
			} else {
				$this->addFlash('alert', $this->trans('User with the specified login or e-mail not found.'));
			}
		}
		$dataTable = $repository->createDataTable();
		return $this->render($this->crudInfo->getTemplateLocation() . 'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'form' => $jumpForm->createView()
		));
	}

	/**
	 * @Route("/ajax-list", name="admin_user_ajax_list")
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
	 * @Route("/{id}/info", name="admin_user_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		return $action->run($this, $id, function(User $user) {
			$repository = $this->get(self::REPOSITORY_NAME);
			return ['places' => $repository->findPlaces($user)];
		});
	}

	/**
	 * @Route("/{id}/edit", name="admin_user_edit")
	 */
	public function editAction($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, AdminUserForm::class);
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
