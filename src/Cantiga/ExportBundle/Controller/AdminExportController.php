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

namespace Cantiga\ExportBundle\Controller;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\AdminPageController;
use Cantiga\ExportBundle\Entity\DataExport;
use Cantiga\ExportBundle\Form\DataExportForm;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/admin/export")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminExportController extends AdminPageController
{

	const REPOSITORY_NAME = 'cantiga.export.repo.export';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->crudInfo = $this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('CantigaExportBundle:AdminExport:')
			->setItemNameProperty('name')
			->setPageTitle('Export settings')
			->setPageSubtitle('Configure data export to external systems via REST')
			->setIndexPage('admin_export_index')
			->setInfoPage('admin_export_info')
			->setInsertPage('admin_export_insert')
			->setEditPage('admin_export_edit')
			->setRemovePage('admin_export_remove')
			->setItemCreatedMessage('ExportSettingsCreated: 0')
			->setItemUpdatedMessage('ExportSettingsUpdated: 0')
			->setItemRemovedMessage('ExportSettingsRemoved: 0')
			->setRemoveQuestion('ExportSettingsRemoveQuestion: 0');

		$this->breadcrumbs()
			->workgroup('projects')
			->entryLink($this->trans('Export settings', [], 'pages'), $this->crudInfo->getIndexPage());
	}

	/**
	 * @Route("/index", name="admin_export_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
		return $this->render($this->crudInfo->getTemplateLocation() . 'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'insertPage' => $this->crudInfo->getInsertPage(),
			'ajaxListPage' => 'admin_export_ajax_list',
		));
	}

	/**
	 * @Route("/ajax-list", name="admin_export_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', 'admin_export_info', ['id' => '::id'])
			->link('edit_link', 'admin_export_edit', ['id' => '::id'])
			->link('remove_link', 'admin_export_remove', ['id' => '::id']);

		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($dataTable)));
	}

	/**
	 * @Route("/ajax-status", name="admin_export_ajax_status")
	 */
	public function ajaxStatusAction(Request $request)
	{
		$p = $request->get('p');
		try {
			$projectRepo = $this->get('cantiga.core.repo.project');
			$statusRepo = $this->get('cantiga.core.repo.project_area_status');

			$project = $projectRepo->getItem($p);
			return new JsonResponse($statusRepo->getFormChoices($project));
		} catch (Exception $ex) {
			return new JsonResponse([]);
		}
	}

	/**
	 * @Route("/{id}/info", name="admin_export_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		return $action->run($this, $id);
	}

	/**
	 * @Route("/insert", name="admin_export_insert")
	 */
	public function insertAction(Request $request)
	{
		$action = new InsertAction($this->crudInfo, new DataExport(), DataExportForm::class, [
			'projectRepository' => $this->get('cantiga.core.repo.project'),
			'areaStatusRepository' => $this->get('cantiga.core.repo.project_area_status')
		]);
		return $action->run($this, $request);
	}

	/**
	 * @Route("/{id}/edit", name="admin_export_edit")
	 */
	public function editAction($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, DataExportForm::class, [
			'projectRepository' => $this->get('cantiga.core.repo.project'),
			'areaStatusRepository' => $this->get('cantiga.core.repo.project_area_status')
		]);
		return $action->run($this, $id, $request);
	}

	/**
	 * @Route("/{id}/remove", name="admin_export_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		return $action->run($this, $id, $request);
	}

}
