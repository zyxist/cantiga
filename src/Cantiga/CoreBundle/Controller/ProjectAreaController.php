<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\CoreSettings;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Form\ProjectAreaForm;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/area")
 * @Security("has_role('ROLE_PROJECT_MEMBER')")
 */
class ProjectAreaController extends ProjectPageController
{
	use InformationTrait;
	
	const REPOSITORY_NAME = 'cantiga.core.repo.project_area';
	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->crudInfo = $this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('CantigaCoreBundle:ProjectArea:')
			->setItemNameProperty('name')
			->setPageTitle('Areas')
			->setPageSubtitle('Manage the areas in this project')
			->setIndexPage('project_area_index')
			->setInfoPage('project_area_info')
			->setInsertPage('project_area_insert')
			->setEditPage('project_area_edit');
		
		$this->breadcrumbs()
			->workgroup('data')
			->entryLink($this->trans('Areas', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		$this->get(self::REPOSITORY_NAME)->setActiveProject($this->getActiveProject());
	}
		
	/**
	 * @Route("/index", name="project_area_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
        return $this->render($this->crudInfo->getTemplateLocation().'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale()
		));
	}
	
	/**
	 * @Route("/ajax-list", name="project_area_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', 'project_area_info', ['id' => '::id', 'slug' => $this->getSlug()])
			->link('edit_link', 'project_area_edit', ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
        return new JsonResponse($routes->process($repository->listData($dataTable, $this->getTranslator())));
	}
	
	/**
	 * @Route("/{id}/ajax-list", name="project_area_ajax_members")
	 */
	public function ajaxMembersAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);
			return new JsonResponse(['status' => 1, 'data' => $repository->findMembers($item)]);
		} catch(ItemNotFoundException $exception) {
			return new JsonResponse(['status' => 0]);
		}
	}
	
	/**
	 * @Route("/{id}/info", name="project_area_info")
	 */
	public function infoAction($id, Request $request)
	{
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id, function(Area $item) use($request) {
			$html = $this->renderInformationExtensions(CoreExtensions::PROJECT_AREA_INFORMATION, $request, $item);			
			$formModel = $this->extensionPointFromSettings(CoreExtensions::AREA_FORM, CoreSettings::AREA_FORM);
			return [
				'summary' => $formModel->createSummary(),
				'extensions' => $html,
			];
		});
	}
	 
	/**
	 * @Route("/insert", name="project_area_insert")
	 */
	public function insertAction(Request $request)
	{
		$territoryRepo = $this->get('cantiga.core.repo.project_territory');
		$statusRepo = $this->get('cantiga.core.repo.project_area_status');
		$groupRepo = $this->get('cantiga.core.repo.project_group');
		$territoryRepo->setProject($this->getActiveProject());
		$statusRepo->setProject($this->getActiveProject());
		$groupRepo->setProject($this->getActiveProject());
		
		$formModel = $this->extensionPointFromSettings(CoreExtensions::AREA_FORM, CoreSettings::AREA_FORM);
		$item = new Area();
		$item->setReporter($this->getUser());
		$item->setProject($this->getActiveProject());
		$action = new InsertAction($this->crudInfo, $item, new ProjectAreaForm($formModel, $territoryRepo, $groupRepo, $statusRepo));
		$action->slug($this->getSlug());
		$action->customForm($formModel);
		return $action->run($this, $request);
	}
	
	/**
	 * @Route("/{id}/edit", name="project_area_edit")
	 */
	public function editAction($id, Request $request)
	{
		$territoryRepo = $this->get('cantiga.core.repo.project_territory');
		$statusRepo = $this->get('cantiga.core.repo.project_area_status');
		$groupRepo = $this->get('cantiga.core.repo.project_group');
		$territoryRepo->setProject($this->getActiveProject());
		$statusRepo->setProject($this->getActiveProject());
		$groupRepo->setProject($this->getActiveProject());
		
		$formModel = $this->extensionPointFromSettings(CoreExtensions::AREA_FORM, CoreSettings::AREA_FORM);
		$action = new EditAction($this->crudInfo, new ProjectAreaForm($formModel, $territoryRepo, $groupRepo, $statusRepo));
		$action->slug($this->getSlug());
		$action->customForm($formModel);
		return $action->run($this, $id, $request);
	}
}