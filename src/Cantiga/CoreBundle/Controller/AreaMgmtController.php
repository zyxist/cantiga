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

use Cantiga\Components\Hierarchy\Entity\Member;
use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Controller\WorkspaceController;
use Cantiga\CoreBundle\Controller\Traits\InformationTrait;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\CoreSettings;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\CoreBundle\Event\ContextMenuEvent;
use Cantiga\CoreBundle\Form\AreaForm;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/s/{slug}/areas")
 * @Security("is_granted('PLACE_MEMBER') and (is_granted('MEMBEROF_PROJECT') or is_granted('MEMBEROF_GROUP'))")
 */
class AreaMgmtController extends WorkspaceController
{
	use InformationTrait;

	const REPOSITORY_NAME = 'cantiga.core.repo.area_mgmt';
	const FILTER_NAME = 'cantiga.core.filter.area';
	const API_LIST_LINK = 'area_mgmt_api_list';
	const API_MEMBERS_LINK = 'area_mgmt_api_members';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;
	private $customizableGroup;
	private $showCreateLink;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->crudInfo = $this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('CantigaCoreBundle:AreaMgmt:')
			->setItemNameProperty('name')
			->setPageTitle('Areas')
			->setPageSubtitle('Manage the areas')
			->setIndexPage('area_mgmt_index')
			->setInfoPage('area_mgmt_info')
			->setInsertPage('area_mgmt_insert')
			->setEditPage('area_mgmt_edit')
			->setItemUpdatedMessage('The area profile \'0\' has been updated.');

		$this->breadcrumbs()
			->workgroup('data')
			->entryLink($this->trans('Areas', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		$this->get(self::REPOSITORY_NAME)->setParentPlace($this->get('cantiga.user.membership.storage')->getMembership()->getPlace());
		$this->checkCapabilities();
	}

	/**
	 * @Route("/index", name="area_mgmt_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$filter = $this->get(self::FILTER_NAME);
		$filter->setTargetProject($this->getActiveProject());
		$filterForm = $filter->createForm($this->createFormBuilder($filter));
		$filterForm->handleRequest($request);

		$dataTable = $repository->createDataTable();
		$dataTable->filter($filter);
		return $this->render($this->crudInfo->getTemplateLocation() . 'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'ajaxListPage' => self::API_LIST_LINK,
			'filterForm' => $filterForm->createView(),
			'filter' => $filter,
			'showCreateLink' => $this->showCreateLink,
			'customizableGroup' => $this->customizableGroup
		));
	}

	/**
	 * @Route("/list", name="area_mgmt_api_list")
	 */
	public function apiListAction(Request $request)
	{
		$filter = $this->get(self::FILTER_NAME);
		$filter->setTargetProject($this->getActiveProject());
		$filterForm = $filter->createForm($this->createFormBuilder($filter));
		$filterForm->handleRequest($request);

		$routes = $this->dataRoutes()
			->link('info_link', $this->crudInfo->getInfoPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('edit_link', $this->crudInfo->getEditPage(), ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
		$dataTable->filter($filter);
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($dataTable, $this->getTranslator())));
	}

	/**
	 * @Route("/{id}/members", name="area_mgmt_api_members")
	 */
	public function apiMembersAction($id, Request $request, Membership $membership)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);
			return new JsonResponse(['status' => 1, 'data' => Member::collectionAsArray($repository->findMembers($item), function(array $user) use($membership) {
				$user['url'] = $this->generateUrl('memberlist_profile', ['slug' => $this->getSlug(), 'id' => $user['id']]);
				if (!$membership->getShowDownstreamContactData()) {
					$user['contactMail'] = '';
					$user['contactTelephone'] = '';
					$user['userNotes'] = '';
				}
				return $user;
			})]);
		} catch (ItemNotFoundException $exception) {
			return new JsonResponse(['status' => 0]);
		}
	}

	/**
	 * @Route("/{id}/info", name="area_mgmt_info")
	 */
	public function infoAction($id, Request $request)
	{		
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug());
		$action->set('ajaxMembersPage', self::API_MEMBERS_LINK);
		return $action->run($this, $id, function(Area $item) use($request) {
				$event = $this->get('event_dispatcher')->dispatch(CantigaEvents::UI_CTXMENU_PROJECT_AREA, new ContextMenuEvent($item));
				$html = $this->renderInformationExtensions(CoreExtensions::AREA_INFORMATION, $request, $item);
				$formModel = $this->extensionPointFromSettings(CoreExtensions::AREA_FORM, CoreSettings::AREA_FORM);
				return [
					'progressBarColor' => (
						$item->getPercentCompleteness() < 50 ? 'red' :
						($item->getPercentCompleteness() < 80 ? 'orange' : 'green')
					),
					'summary' => $formModel->createSummary(),
					'extensions' => $html,
					'links' => $event->getLinks()
				];
			});
	}

	/**
	 * @Route("/insert", name="area_mgmt_insert")
	 * @Security("is_granted('MEMBEROF_PROJECT')")
	 */
	public function insertAction(Request $request, Membership $membership)
	{
		$formModel = $this->extensionPointFromSettings(CoreExtensions::AREA_FORM, CoreSettings::AREA_FORM);
		$item = new Area();
		$item->setReporter($this->getUser());
		$item->setProject($this->getActiveProject());
		
		if (!$this->customizableGroup) {
			$item->setGroup($membership->getPlace());
		}
		
		$action = new InsertAction($this->crudInfo, $item, AreaForm::class, $this->createFormConfig($formModel));
		$action->slug($this->getSlug());
		$action->customForm($formModel);
		$action->set('customizableGroup', $this->customizableGroup);
		return $action->run($this, $request);
	}

	/**
	 * @Route("/{id}/edit", name="area_mgmt_edit")
	 */
	public function editAction($id, Request $request)
	{
		$formModel = $this->extensionPointFromSettings(CoreExtensions::AREA_FORM, CoreSettings::AREA_FORM);
		$action = new EditAction($this->crudInfo, AreaForm::class, $this->createFormConfig($formModel));
		$action->slug($this->getSlug());
		$action->customForm($formModel);
		$action->set('customizableGroup', $this->customizableGroup);
		return $action->run($this, $id, $request);
	}
	
	private function createFormConfig($formModel): array
	{
		$territoryRepo = $this->get('cantiga.core.repo.project_territory');
		$statusRepo = $this->get('cantiga.core.repo.project_area_status');
		$territoryRepo->setProject($this->getActiveProject());
		$statusRepo->setProject($this->getActiveProject());		
		$base = [
			'customFormModel' => $formModel,
			'territoryRepository' => $territoryRepo,
			'statusRepository' => $statusRepo
		];
		
		if ($this->isGranted('MEMBEROF_PROJECT')) {
			$groupRepo = $this->get('cantiga.core.repo.project_group');
			$groupRepo->setProject($this->getActiveProject());
			$base['groupRepository'] = $groupRepo;
		}
		return $base;
	}
	
	private function checkCapabilities()
	{
		if ($this->isGranted('MEMBEROF_PROJECT')) {
			$this->showCreateLink = true;
			$this->customizableGroup = true;
		} else {
			$this->showCreateLink = false;
			$this->customizableGroup = false;
		}
	}
}
