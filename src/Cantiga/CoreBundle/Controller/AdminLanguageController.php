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
use Cantiga\CoreBundle\Entity\Language;
use Cantiga\CoreBundle\Form\AdminLanguageForm;

/**
 * @Route("/admin/languages")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminLanguageController extends AdminPageController
{

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->setLocale($request);
		$this->crudInfo = $this->newCrudInfo('cantiga.core.repo.language')
			->setTemplateLocation('CantigaCoreBundle:AdminLanguage:')
			->setItemNameProperty('name')
			->setPageTitle('Languages')
			->setPageSubtitle('Manage the system languages available for the users')
			->setIndexPage('admin_language_index')
			->setInfoPage('admin_language_info')
			->setInsertPage('admin_language_insert')
			->setEditPage('admin_language_edit')
			->setRemovePage('admin_language_remove');

		$this->breadcrumbs()
			->workgroup('settings')
			->entryLink($this->trans('Languages', [], 'pages'), $this->crudInfo->getIndexPage());
	}

	/**
	 * @Route("/index", name="admin_language_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get('cantiga.core.repo.language');
		$dataTable = $repository->createDataTable();
		return $this->render('CantigaCoreBundle:AdminLanguage:index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale()
		));
	}

	/**
	 * @Route("/ajax-list", name="admin_language_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', 'admin_language_info', ['id' => '::id'])
			->link('edit_link', 'admin_language_edit', ['id' => '::id'])
			->link('remove_link', 'admin_language_remove', ['id' => '::id']);

		$repository = $this->get('cantiga.core.repo.language');
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($dataTable)));
	}

	/**
	 * @Route("/{id}/info", name="admin_language_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		return $action->run($this, $id);
	}

	/**
	 * @Route("/insert", name="admin_language_insert")
	 */
	public function insertAction(Request $request)
	{
		$action = new InsertAction($this->crudInfo, new Language(), AdminLanguageForm::class);
		return $action->run($this, $request);
	}

	/**
	 * @Route("/{id}/edit", name="admin_language_edit")
	 */
	public function editAction($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, AdminLanguageForm::class);
		return $action->run($this, $id, $request);
	}

	/**
	 * @Route("/{id}/remove", name="admin_language_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		return $action->run($this, $id, $request);
	}

}
