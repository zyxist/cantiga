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

namespace Cantiga\MilestoneBundle\Controller;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\MilestoneBundle\Entity\MilestoneStatusRule;
use Cantiga\MilestoneBundle\Form\MilestoneStatusRuleForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/milestone/status-rules")
 * @Security("is_granted('PLACE_MANAGER')")
 */
class ProjectMilestoneStatusRuleController extends ProjectPageController
{

	const REPOSITORY_NAME = 'cantiga.milestone.repo.status_rule';
	const STATUS_REPO = 'cantiga.core.repo.project_area_status';
	const MILESTONE_REPO = 'cantiga.milestone.repo.milestone';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setProject($this->getActiveProject());
		$this->crudInfo = $this->newCrudInfo($repository)
			->setTemplateLocation('CantigaMilestoneBundle:ProjectMilestoneStatusRule:')
			->setItemNameProperty('name')
			->setPageTitle('Status rules')
			->setPageSubtitle('Create the automatic rules for setting the area status')
			->setIndexPage('project_milestone_status_rule_index')
			->setInfoPage('project_milestone_status_rule_info')
			->setInsertPage('project_milestone_status_rule_insert')
			->setEditPage('project_milestone_status_rule_edit')
			->setRemovePage('project_milestone_status_rule_remove')
			->setItemCreatedMessage('The status rule \'0\' has been created.')
			->setItemUpdatedMessage('The status rule \'0\' has been updated.')
			->setItemRemovedMessage('The status rule \'0\' has been removed.')
			->setRemoveQuestion('Do you really want to remove the status rule \'0\'?');

		$this->breadcrumbs()
			->workgroup('manage')
			->entryLink($this->trans('Status rules', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="project_milestone_status_rule_index")
	 */
	public function indexAction(Request $request)
	{
		$dataTable = $this->crudInfo->getRepository()->createDataTable();
		return $this->render($this->crudInfo->getTemplateLocation() . 'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'insertPage' => $this->crudInfo->getInsertPage(),
			'ajaxListPage' => 'project_milestone_status_rule_ajax_list'
		));
	}

	/**
	 * @Route("/ajax-list", name="project_milestone_status_rule_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', $this->crudInfo->getInfoPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('edit_link', $this->crudInfo->getEditPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('remove_link', $this->crudInfo->getRemovePage(), ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->crudInfo->getRepository();
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($dataTable, $this->getTranslator())));
	}

	/**
	 * @Route("/{id}/info", name="project_milestone_status_rule_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id);
	}

	/**
	 * @Route("/insert", name="project_milestone_status_rule_insert")
	 */
	public function insertAction(Request $request)
	{
		$statusRepo = $this->get(self::STATUS_REPO);
		$milestoneRepo = $this->get(self::MILESTONE_REPO);
		$statusRepo->setProject($this->getActiveProject());
		$milestoneRepo->setProject($this->getActiveProject());
		
		$entity = new MilestoneStatusRule();
		$entity->setProject($this->getActiveProject());

		$action = new InsertAction($this->crudInfo, $entity, MilestoneStatusRuleForm::class, ['statusRepository' => $statusRepo, 'milestoneRepository' => $milestoneRepo]);
		$action->slug($this->getSlug());
		return $action->run($this, $request);
	}

	/**
	 * @Route("/{id}/edit", name="project_milestone_status_rule_edit")
	 */
	public function editAction($id, Request $request)
	{
		$statusRepo = $this->get(self::STATUS_REPO);
		$milestoneRepo = $this->get(self::MILESTONE_REPO);
		$statusRepo->setProject($this->getActiveProject());
		$milestoneRepo->setProject($this->getActiveProject());
		
		$action = new EditAction($this->crudInfo, MilestoneStatusRuleForm::class, ['statusRepository' => $statusRepo, 'milestoneRepository' => $milestoneRepo]);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}

	/**
	 * @Route("/{id}/remove", name="project_milestone_status_rule_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}

}
