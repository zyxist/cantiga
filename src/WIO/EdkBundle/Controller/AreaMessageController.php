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

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Controller\AreaPageController;
use Cantiga\Metamodel\Exception\ModelException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use WIO\EdkBundle\EdkTexts;

/**
 * @Route("/area/{slug}/participants/messages")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class AreaMessageController extends AreaPageController
{
	const REPOSITORY_NAME = 'wio.edk.repo.message';

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
			->setTemplateLocation('WioEdkBundle:AreaMessage:')
			->setItemNameProperty('subject')
			->setPageTitle('Messages')
			->setPageSubtitle('View messages sent you by the participants')
			->setIndexPage('area_edk_message_index')
			->setInfoPage('area_edk_message_info');

		$this->breadcrumbs()
			->workgroup('participants')
			->entryLink($this->trans('Messages', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="area_edk_message_index")
	 */
	public function indexAction(Request $request)
	{
		$text = $this->getTextRepository()->getTextOrFalse(EdkTexts::MESSAGE_TEXT, $request, $this->getActiveProject());
		$dataTable = $this->crudInfo->getRepository()->createDataTable();
		return $this->render($this->crudInfo->getTemplateLocation() . 'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'insertPage' => $this->crudInfo->getInsertPage(),
			'ajaxListPage' => 'area_edk_message_ajax_list',
			'text' => $text,
		));
	}

	/**
	 * @Route("/ajax-list", name="area_edk_message_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', $this->crudInfo->getInfoPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->linkGenerator('assignee_link', function(RouterInterface $router, array $row) {
				if (!empty($row['responderId'])) {
					return $router->generate('memberlist_profile', ['id' => $row['responderId'], 'slug' => $this->getSlug()]);
				}
				return null;
			});

		$repository = $this->crudInfo->getRepository();
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($dataTable, $this->getTranslator())));
	}

	/**
	 * @Route("/{id}/info", name="area_edk_message_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id, function($item) {
			return [
				'transitions' => $item->getAllowedTransitions($this->getUser(), $this->isGranted('PLACE_MANAGER')),
				'currentUser' => $this->getUser(),
			];
		});
	}
	
	/**
	 * @Route("/{id}/transit/{status}", name="area_edk_message_transit")
	 */
	public function changeStateAction($id, $status)
	{
		try {
			$item = $this->crudInfo->getRepository()->getItem($id);
			$this->crudInfo->getRepository()->changeState($item, $this->getUser(), $this->isGranted('PLACE_MANAGER'), $status);
			return $this->showPageWithMessage($this->trans('MsgTransitionCompleted', [], 'edk'), 'area_edk_message_info', ['slug' => $this->getSlug(), 'id' => $item->getId()]);
		} catch (ModelException $ex) {
			return $this->showPageWithError($this->trans($ex->getMessage(), [], 'edk'), 'area_edk_message_index', ['slug' => $this->getSlug()]);
		}
	}
	
	/**
	 * @Route("/{id}/duplicate", name="area_edk_message_duplicate")
	 */
	public function duplicateAction($id)
	{
		try {
			$item = $this->crudInfo->getRepository()->getItem($id);
			$this->crudInfo->getRepository()->changeDuplicateFlag($item);
			return $this->showPageWithMessage($this->trans('MsgDuplicateStatusChanged', [], 'edk'), 'area_edk_message_info', ['slug' => $this->getSlug(), 'id' => $item->getId()]);
		} catch (ModelException $ex) {
			return $this->showPageWithError($this->trans($ex->getMessage(), [], 'edk'), 'area_edk_message_index', ['slug' => $this->getSlug()]);
		}
	}
}
