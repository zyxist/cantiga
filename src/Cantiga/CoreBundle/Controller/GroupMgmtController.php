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
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\WorkspaceController;
use Cantiga\CoreBundle\Controller\Traits\InformationTrait;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Form\ProjectGroupForm;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/s/{slug}/group")
 * @Security("is_granted('PLACE_MEMBER') and is_granted('MEMBEROF_PROJECT')")
 */
class GroupMgmtController extends WorkspaceController
{
	use InformationTrait;

	const REPOSITORY_NAME = 'cantiga.core.repo.project_group';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->crudInfo = $this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('CantigaCoreBundle:ProjectGroup:')
			->setItemNameProperty('name')
			->setPageTitle('Groups')
			->setPageSubtitle('Organize the areas and users into groups to ease the management')
			->setIndexPage('group_mgmt_index')
			->setInfoPage('group_mgmt_info')
			->setInsertPage('group_mgmt_insert')
			->setEditPage('group_mgmt_edit')
			->setRemovePage('group_mgmt_remove')
			->setItemCreatedMessage('The group \'0\' has been created.')
			->setItemUpdatedMessage('The group \'0\' has been updated.')
			->setItemRemovedMessage('The group \'0\' has been removed.')
			->setRemoveQuestion('Do you really want to remove group \'0\'?');

		$this->breadcrumbs()
			->workgroup('data')
			->entryLink($this->trans('Groups', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		$this->get(self::REPOSITORY_NAME)->setProject($this->getActiveProject());
	}

	/**
	 * @Route("/index", name="group_mgmt_index")
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
			'ajaxListPage' => 'group_mgmt_api_list',
		));
	}

	/**
	 * @Route("/api/list", name="group_mgmt_api_list")
	 */
	public function apiListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', $this->crudInfo->getInfoPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('edit_link', $this->crudInfo->getEditPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('remove_link', $this->crudInfo->getRemovePage(), ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($dataTable)));
	}

	/**
	 * @Route("/{id}/api/members", name="group_mgmt_api_members")
	 */
	public function apiMembersAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);
			return new JsonResponse(['status' => 1, 'data' => $repository->findMembers($item->getPlace())]);
		} catch (ItemNotFoundException $exception) {
			return new JsonResponse(['status' => 0]);
		}
	}

	/**
	 * @Route("/{id}/info", name="group_mgmt_info")
	 */
	public function infoAction($id, Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id, function($group) use($repository, $request) {
				$html = $this->renderInformationExtensions(CoreExtensions::PROJECT_GROUP_INFORMATION, $request, $group);
				return [
					'areas' => $repository->findGroupAreas($group),
					'extensions' => $html,
				];
			});
	}

	/**
	 * @Route("/insert", name="group_mgmt_insert")
	 */
	public function insertAction(Request $request)
	{
		$item = new Group();
		$item->setProject($this->getActiveProject());
		$action = new InsertAction($this->crudInfo, $item, ProjectGroupForm::class, ['categoryRepository' => $this->getCategoryRepo()]);
		$action->slug($this->getSlug());
		return $action->run($this, $request);
	}

	/**
	 * @Route("/{id}/edit", name="group_mgmt_edit")
	 */
	public function editAction($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, ProjectGroupForm::class, ['categoryRepository' => $this->getCategoryRepo()]);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}

	/**
	 * @Route("/{id}/remove", name="group_mgmt_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}

	private function getCategoryRepo()
	{
		$categoryRepo = $this->get('cantiga.core.repo.project_group_category');
		$categoryRepo->setProject($this->getActiveProject());
		return $categoryRepo;
	}
}
