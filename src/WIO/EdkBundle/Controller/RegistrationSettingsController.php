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
namespace WIO\EdkBundle\Controller;

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Controller\WorkspaceController;
use Cantiga\CoreBundle\Entity\Area;
use DateTimeZone;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use WIO\EdkBundle\EdkTexts;
use WIO\EdkBundle\Form\EdkRegistrationSettingsForm;

/**
 * @Route("/s/{slug}/participants/registration-settings")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class RegistrationSettingsController extends WorkspaceController
{
	const REPOSITORY_NAME = 'wio.edk.repo.registration';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$place = $this->get('cantiga.user.membership.storage')->getMembership()->getPlace();
		
		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setRootEntity($place);
		$this->crudInfo = $this->newCrudInfo($repository)
			->setTemplateLocation('WioEdkBundle:RegistrationSettings:')
			->setItemNameProperty('name')
			->setPageTitle('Registration settings')
			->setPageSubtitle('Manage the registration settings for the routes')
			->setIndexPage('edk_reg_settings_index')
			->setInfoPage('edk_reg_settings_info')
			->setEditPage('edk_reg_settings_edit')
			->setItemUpdatedMessage('The registration settings \'0\' have been updated.');

		$this->breadcrumbs()
			->workgroup('participants')
			->entryLink($this->trans('Registration settings', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="edk_reg_settings_index")
	 */
	public function indexAction(Request $request, Membership $membership)
	{
		$text = $this->getTextRepository()->getTextOrFalse(EdkTexts::REGISTRATION_SETTINGS_TEXT, $request, $this->getActiveProject());
		$dataTable = $this->crudInfo->getRepository()->createDataTable();
		return $this->render($this->crudInfo->getTemplateLocation() . 'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'insertPage' => $this->crudInfo->getInsertPage(),
			'ajaxListPage' => 'edk_reg_settings_api_list',
			'isArea' => $this->isArea($membership),
			'text' => $text
		));
	}

	/**
	 * @Route("/api-list", name="edk_reg_settings_api_list")
	 */
	public function apiListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', $this->crudInfo->getInfoPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('edit_link', $this->crudInfo->getEditPage(), ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->crudInfo->getRepository();
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($dataTable, $this->getTranslator())));
	}

	/**
	 * @Route("/{id}/info", name="edk_reg_settings_info")
	 */
	public function infoAction($id, Request $request, Membership $membership)
	{
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug());
		$action->set('isArea', $this->isArea($membership));
		$action->set('routeInfoLink', 'edk_route_info');
		$action->set('areaInfoLink', 'area_mgmt_info');
		return $action->run($this, $id);
	}

	/**
	 * @Route("/{id}/edit", name="edk_reg_settings_edit")
	 */
	public function editAction($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, EdkRegistrationSettingsForm::class, ['timezone' => new DateTimeZone($this->getUser()->getSettingsTimezone())]);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}
	
	private function isArea(Membership $membership)
	{
		return ($membership->getPlace() instanceof Area);
	}
}
