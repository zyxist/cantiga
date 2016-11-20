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
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\QuestionHelper;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\AdminPageController;
use Cantiga\Metamodel\Exception\ModelException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/admin/registration")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminRegistrationController extends AdminPageController
{
	const REPOSITORY_NAME = 'cantiga.core.repo.user_registration';
	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->crudInfo = $this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('CantigaCoreBundle:AdminRegistration:')
			->setItemNameProperty('name')
			->setPageTitle('User registrations')
			->setPageSubtitle('See awaiting user registration requests')
			->setIndexPage('admin_registration_index')
			->setInfoPage('admin_registration_info')
			->setRemovePage('admin_registration_remove')
			->setRemoveQuestion('RegistrationRemovalQuestionText');
		
		$this->breadcrumbs()
			->workgroup('access')
			->entryLink($this->trans('User registrations', [], 'pages'), $this->crudInfo->getIndexPage());
	}
		
	/**
	 * @Route("/index", name="admin_registration_index")
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
	 * @Route("/ajax-list", name="admin_registration_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', $this->crudInfo->getInfoPage(), ['id' => '::id'])
			->link('remove_link', $this->crudInfo->getRemovePage(), ['id' => '::id']);

		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($dataTable)));
	}
	
	/**
	 * @Route("/{id}/info", name="admin_registration_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		return $action->run($this, $id);
	}
		
	/**
	 * @Route("/{id}/remove", name="admin_registration_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		return $action->run($this, $id, $request);
	}
	
	/**
	 * @Route("/prune", name="admin_registration_prune")
	 */
	public function pruneAction(Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			
			$question = new QuestionHelper($this->trans('PruneRegistrationsQuestionText'));
			$question->onSuccess(function() use($repository) {
				$number = $repository->pruneOld();
				$this->get('session')->getFlashBag()->add('info', $this->trans('PrunedRegistrations: 0', [$number]));
			});
			$question->respond('admin_registration_prune');
			$question->path($this->crudInfo->getIndexPage());
			$question->title($this->trans($this->crudInfo->getPageTitle()), $this->crudInfo->getPageSubtitle());
			$this->breadcrumbs()->link($this->trans('Prune', [], 'general'), 'admin_registration_prune');
			return $question->handleRequest($this, $request);
		} catch(ModelException $exception) {
			return $this->showPageWithError($exception->getMessage(), $this->crudInfo->getIndexPage());
		}
	}
}