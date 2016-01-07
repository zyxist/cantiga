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
namespace WIO\EdkBundle\Controller\Traits;

use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Message;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use WIO\EdkBundle\Entity\EdkRoute;
use WIO\EdkBundle\Form\EdkRouteForm;

/**
 * Common code for EWC route controllers.
 *
 * @author Tomasz Jędrzejewski
 */
trait RouteTrait
{
	protected function performIndex(Request $request)
	{
		$dataTable = $this->crudInfo->getRepository()->createDataTable();
        return $this->render($this->crudInfo->getTemplateLocation().'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'insertPage' => $this->crudInfo->getInsertPage(),
			'ajaxListPage' => self::AJAX_LIST_PAGE,
			'isArea' => $this->isArea(),
		));
	}
	
	protected function performAjaxList(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', $this->crudInfo->getInfoPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('edit_link', $this->crudInfo->getEditPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('remove_link', $this->crudInfo->getRemovePage(), ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->crudInfo->getRepository();
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
        return new JsonResponse($routes->process($repository->listData($dataTable)));
	}
	
	protected function performInfo($id)
	{
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug())
			->set('ajaxReloadPage', self::AJAX_RELOAD_PAGE)
			->set('ajaxUpdatePage', self::AJAX_UPDATE_PAGE)
			->set('ajaxChatFeedPage', self::AJAX_FEED_PAGE)
			->set('ajaxChatPostPage', self::AJAX_POST_PAGE)
			->set('isArea', $this->isArea());
		return $action->run($this, $id);
	}
	 
	protected function performInsert(Request $request)
	{
		$entity = new EdkRoute();		
		$action = new InsertAction($this->crudInfo, $entity, new EdkRouteForm(EdkRouteForm::ADD));
		$action->slug($this->getSlug());
		return $action->run($this, $request);
	}

	protected function performEdit($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, new EdkRouteForm(EdkRouteForm::EDIT));
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}
	
	protected function performRemove($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}

	protected function performAjaxReload($id, Request $request)
	{
		try {
			$route = $this->crudInfo->getRepository()->getItem($id);
			return new JsonResponse(['success' => 1, 'notes' => $route->getFullNoteInformation($this->getTranslator())]);
		} catch (Exception $ex) {
			return new JsonResponse(['success' => 0]);
		}
	}
	
	protected function performAjaxUpdate($id, Request $request)
	{
		try {
			$i = $request->get('i');
			$c = $request->get('c');
			if (empty($c)) {
				$c = null;
			}
			
			$route = $this->crudInfo->getRepository()->getItem($id);
			$route->saveEditableNote($this->get('database_connection'), $i, $c);
			return new JsonResponse(['success' => 1, 'note' => $route->getFullEditableNote($this->getTranslator(), $i)]);
		} catch (Exception $ex) {
			return new JsonResponse(['success' => 0]);
		}
	}

	protected function performAjaxFeed($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);
			return new JsonResponse($repository->getComments($item));
		} catch (Exception $ex) {
			return new JsonResponse(['status' => 0]);
		}
	}
	
	protected function performAjaxPost($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);
			$message = $request->get('message');
			if (!empty($message)) {
				$item->post(new Message($this->getUser(), $message));
				$repository->update($item);
			}
			return new JsonResponse($repository->getComments($item));
		} catch (Exception $ex) {
			return new JsonResponse(['status' => 0]);
		}
	}
	
	private function isArea()
	{
		return ($this->getMembership()->getItem() instanceof Area);
	}
}
