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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\AdminPageController;
use Cantiga\CoreBundle\Entity\AppMail;
use Cantiga\CoreBundle\Form\AdminAppMailForm;

/**
 * @Route("/admin/app/mail")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminAppMailController extends AdminPageController
{

	const REPOSITORY_NAME = 'cantiga.core.repo.mail';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->crudInfo = $this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('CantigaCoreBundle:AdminAppMail:')
			->setItemNameProperty('subject')
			->setPageTitle('Mail templates')
			->setPageSubtitle('Manage the messages sent to the users')
			->setIndexPage('admin_app_mail_index')
			->setInfoPage('admin_app_mail_info')
			->setInsertPage('admin_app_mail_insert')
			->setEditPage('admin_app_mail_edit')
			->setRemovePage('admin_app_mail_remove')
			->setRemoveQuestion('Do you really want to remove \'0\' item?');

		$this->breadcrumbs()
			->workgroup('settings')
			->entryLink($this->trans('Mail templates', [], 'pages'), $this->crudInfo->getIndexPage());
	}

	/**
	 * @Route("/index", name="admin_app_mail_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
		return $this->render('CantigaCoreBundle:AdminAppMail:index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale()
		));
	}

	/**
	 * @Route("/ajax-list", name="admin_app_mail_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', 'admin_app_mail_info', ['id' => '::id'])
			->link('edit_link', 'admin_app_mail_edit', ['id' => '::id'])
			->link('remove_link', 'admin_app_mail_remove', ['id' => '::id']);

		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($dataTable)));
	}

	/**
	 * @Route("/{id}/info", name="admin_app_mail_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		return $action->run($this, $id);
	}

	/**
	 * @Route("/insert", name="admin_app_mail_insert")
	 */
	public function insertAction(Request $request)
	{
		$action = new InsertAction($this->crudInfo, new AppMail(), AdminAppMailForm::class);
		return $action->run($this, $request);
	}

	/**
	 * @Route("/{id}/edit", name="admin_app_mail_edit")
	 */
	public function editAction($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, AdminAppMailForm::class);
		return $action->run($this, $id, $request);
	}

	/**
	 * @Route("/{id}/remove", name="admin_app_mail_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		return $action->run($this, $id, $request);
	}

}
