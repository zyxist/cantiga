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
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\AreaPageController;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use DateInterval;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use WIO\EdkBundle\EdkSettings;
use WIO\EdkBundle\EdkTexts;
use WIO\EdkBundle\Entity\EdkParticipant;
use WIO\EdkBundle\Form\EdkParticipantForm;

/**
 * @Route("/area/{slug}/participants/registered")
 * @Security("is_granted('PLACE_PD_ADMIN')")
 */
class AreaParticipantController extends AreaPageController
{
	const REPOSITORY_NAME = 'wio.edk.repo.participant';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$place = $this->get('cantiga.user.membership.storage')->getMembership()->getPlace();
		
		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setArea($place);
		$this->crudInfo = $this->newCrudInfo($repository)
			->setTemplateLocation('WioEdkBundle:AreaParticipant:')
			->setItemNameProperty('name')
			->setPageTitle('Participants')
			->setPageSubtitle('Manage registered participants')
			->setIndexPage('area_edk_participant_index')
			->setInfoPage('area_edk_participant_info')
			->setInsertPage('area_edk_participant_insert')
			->setEditPage('area_edk_participant_edit')
			->setRemovePage('area_edk_participant_remove')
			->setItemCreatedMessage('The participant \'0\' has been created.')
			->setItemUpdatedMessage('The participant \'0\' has been updated.')
			->setItemRemovedMessage('The participant \'0\' has been removed.')
			->setRemoveQuestion('Do you really want to remove the participant \'0\'?');

		$this->breadcrumbs()
			->workgroup('participants')
			->entryLink($this->trans('Participants', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="area_edk_participant_index")
	 */
	public function indexAction(Request $request)
	{
		$text = $this->getTextRepository()->getTextOrFalse(EdkTexts::PARTICIPANT_TEXT, $request, $this->getActiveProject());
		$dataTable = $this->crudInfo->getRepository()->createDataTable();
		return $this->render($this->crudInfo->getTemplateLocation() . 'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'insertPage' => $this->crudInfo->getInsertPage(),
			'ajaxListPage' => 'area_edk_participant_ajax_list',
			'exportCsvPage' => 'area_edk_participant_export_all',
			'text' => $text,
		));
	}

	/**
	 * @Route("/ajax-list", name="area_edk_participant_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', $this->crudInfo->getInfoPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('route_link', 'edk_route_info', ['id' => '::routeId', 'slug' => $this->getSlug()])
			->link('edit_link', $this->crudInfo->getEditPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('remove_link', $this->crudInfo->getRemovePage(), ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->crudInfo->getRepository();
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($dataTable, $this->getTranslator())));
	}
	
	/**
	 * @Route("/ajax-routes", name="area_edk_participant_ajax_routes")
	 */
	public function ajaxRoutesAction(Request $request, Membership $membership)
	{
		try {
			$repository = $this->get('wio.edk.repo.published_data');
			$registrations = $repository->getOpenRegistrations($membership->getPlace(), $this->getProjectSettings()->get(EdkSettings::PUBLISHED_AREA_STATUS)->getValue());
			$response = new JsonResponse($registrations);
			$response->setDate(new DateTime());
			$exp = new DateTime();
			$exp->add(new DateInterval('PT0H5M0S'));
			$response->setExpires($exp);
			return $response;
		} catch(ItemNotFoundException $exception) {
			return new JsonResponse(['success' => 0]);
		}
	}

	/**
	 * @Route("/{id}/info", name="area_edk_participant_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id);
	}
	
	/**
	 * @Route("/insert", name="area_edk_participant_insert")
	 */
	public function insertAction(Request $request, Membership $membership)
	{
		try {
			$area = $membership->getPlace();
			$project = $area->getProject();
			$settingsRepository = $this->get('wio.edk.repo.registration');
			$settingsRepository->setRootEntity($area);

			$entity = EdkParticipant::newParticipant();

			if ($request->getMethod() == 'POST') {
				$entity->setRegistrationSettings($settingsRepository->getItem($request->get('route', null)));
				$entity->setIpAddress(ip2long($_SERVER['REMOTE_ADDR']));
			}

			$action = new InsertAction($this->crudInfo, $entity);
			$action->slug($this->getSlug());
			$action->form(function($controller, $item, $formType, $action) use($request, $project, $settingsRepository) {
				return $controller->createForm(EdkParticipantForm::class, $item, [
					'action' => $action,
					'mode' => EdkParticipantForm::ADD,
					'settingsRepository' => $settingsRepository,
					'texts' => $this->buildTexts($request, $project)
				]);
			});
			$action->set('ajaxRoutePage', 'area_edk_participant_ajax_routes');
			return $action->run($this, $request);
		} catch(ItemNotFoundException $exception) {
			return $this->showPageWithError($this->trans($exception->getMessage(), [], 'edk'), 'area_edk_participant_index', ['slug' => $this->getSlug()]);
		}
	}
	
	/**
	 * @Route("/{id}/edit", name="area_edk_participant_edit")
	 */
	public function editAction($id, Request $request, Membership $membership)
	{
		$settingsRepository = $this->get('wio.edk.repo.registration');
		$settingsRepository->setRootEntity($membership->getPlace());
		
		$action = new EditAction($this->crudInfo);
		$action->form(function($controller, $item, $formType, $action) use($settingsRepository) {
			return $controller->createForm(EdkParticipantForm::class, $item, [
				'action' => $action,
				'mode' => EdkParticipantForm::EDIT,
				'settingsRepository' => $settingsRepository,
				'registrationSettings' => $item->getRegistrationSettings()
			]);
		});
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}
	
	/**
	 * @Route("/{id}/remove", name="area_edk_participant_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}
	
	/**
	 * @Route("/export", name="area_edk_participant_export_all")
	 */
	public function exportAllToCSVAction(Request $request, Membership $membership) {
		$repository = $this->get(self::REPOSITORY_NAME);
		$area = $membership->getPlace();
		$response = new StreamedResponse(function() use($repository, $area) {
			$repository->exportToCSVStream($this->getTranslator(), $area);
		});
		$response->headers->set('Content-Type', 'text/csv');
		$response->headers->set('Cache-Control', '');
		$response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s'));
		$contentDisposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'participants-all-routes.csv');
		$response->headers->set('Content-Disposition', $contentDisposition);
		$response->prepare($request);
		return $response;
	}
	
	private function buildTexts(Request $request, Project $project): array
	{
		return [
			1 => strip_tags($this->getTextRepository()->getText(EdkTexts::REGISTRATION_TERMS1_TEXT, $request, $project)->getContent()),
			2 => strip_tags($this->getTextRepository()->getText(EdkTexts::REGISTRATION_TERMS2_TEXT, $request, $project)->getContent()),
			3 => strip_tags($this->getTextRepository()->getText(EdkTexts::REGISTRATION_TERMS3_TEXT, $request, $project)->getContent())
		];
	}
}
