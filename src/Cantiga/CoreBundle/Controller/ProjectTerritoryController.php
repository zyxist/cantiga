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
use Cantiga\Components\Hierarchy\Importer\ImporterInterface;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CoreBundle\Entity\Territory;
use Cantiga\CoreBundle\Form\ProjectTerritoryForm;
use Cantiga\Metamodel\Exception\ModelException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/territories")
 * @Security("is_granted('PLACE_MANAGER') and is_granted('MEMBEROF_PROJECT')")
 */
class ProjectTerritoryController extends ProjectPageController
{

	const REPOSITORY_NAME = 'cantiga.core.repo.project_territory';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setProject($this->getActiveProject());
		$this->crudInfo = $this->newCrudInfo($repository)
			->setTemplateLocation('CantigaCoreBundle:ProjectTerritory:')
			->setItemNameProperty('name')
			->setPageTitle('Territories')
			->setPageSubtitle('Manage the territories the areas can be assigned to')
			->setIndexPage('project_territory_index')
			->setInfoPage('project_territory_info')
			->setInsertPage('project_territory_insert')
			->setEditPage('project_territory_edit')
			->setRemovePage('project_territory_remove')
			->setItemCreatedMessage('The territory \'0\' has been created.')
			->setItemRemovedMessage('The territory \'0\' has been removed.')
			->setRemoveQuestion('Do you really want to remove the territory \'0\'?');

		$this->breadcrumbs()
			->workgroup('manage')
			->entryLink($this->trans('Territories', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="project_territory_index")
	 */
	public function indexAction(Request $request)
	{
		$dataTable = $this->crudInfo->getRepository()->createDataTable();
		return $this->render($this->crudInfo->getTemplateLocation() . 'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'importer' => $this->getImportService(),
		));
	}

	/**
	 * @Route("/ajax-list", name="project_territory_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', 'project_territory_info', ['id' => '::id', 'slug' => $this->getSlug()])
			->link('edit_link', 'project_territory_edit', ['id' => '::id', 'slug' => $this->getSlug()])
			->link('remove_link', 'project_territory_remove', ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->crudInfo->getRepository();
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($dataTable)));
	}

	/**
	 * @Route("/{id}/info", name="project_territory_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id);
	}

	/**
	 * @Route("/insert", name="project_territory_insert")
	 */
	public function insertAction(Request $request)
	{
		$entity = new Territory();
		$entity->setProject($this->getActiveProject());

		$action = new InsertAction($this->crudInfo, $entity, ProjectTerritoryForm::class);
		$action->slug($this->getSlug());
		return $action->run($this, $request);
	}

	/**
	 * @Route("/{id}/edit", name="project_territory_edit")
	 */
	public function editAction($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, ProjectTerritoryForm::class);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}

	/**
	 * @Route("/{id}/remove", name="project_territory_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}
	
	/**
	 * @Route("/import", name="project_territory_import")
	 */
	public function importAction(Request $request, Membership $membership)
	{
		try {
			$importer = $this->getImportService();
			$repository = $this->get(self::REPOSITORY_NAME);
			if (!$importer->isImportAvailable()) {
				return $this->showPageWithError($this->trans('ImportNotPossibleText'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
			}
			$question = $importer->getImportQuestion($this->crudInfo->getPageTitle(), 'ImportTerritoriesQuestionText');
			$question->path($this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
			$question->respond('project_territory_import', ['slug' => $this->getSlug()]);
			$question->onSuccess(function() use ($repository, $importer) {
				$repository->importFrom($importer->getImportSource(), $importer->getImportDestination());
			});
			$this->breadcrumbs()->link($this->trans('Import', [], 'general'), 'project_territory_import', ['slug' => $this->getSlug()]);
			return $question->handleRequest($this, $request);
		} catch(ModelException $exception) {
			return $this->showPageWithError($exception->getMessage(), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		}
	}
	
	public function getImportService(): ImporterInterface
	{
		return $this->get('cantiga.importer');
	}

}
