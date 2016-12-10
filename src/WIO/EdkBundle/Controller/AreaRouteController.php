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
use Cantiga\CoreBundle\Api\Controller\AreaPageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/area/{slug}/routes")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class AreaRouteController extends AreaPageController
{
	use Traits\RouteTrait;
	
	const REPOSITORY_NAME = 'wio.edk.repo.route';
	const AJAX_LIST_PAGE = 'area_route_ajax_list';
	const AJAX_RELOAD_PAGE = 'area_route_ajax_reload';
	const AJAX_UPDATE_PAGE = 'area_route_ajax_update';
	const AJAX_FEED_PAGE = 'area_route_ajax_feed';
	const AJAX_POST_PAGE = 'area_route_ajax_post';
	const AREA_INFO_PAGE = '';
	
	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setRootEntity($this->getMembership()->getItem());
		$this->crudInfo = $this->newCrudInfo($repository)
			->setTemplateLocation('WioEdkBundle:EdkRoute:')
			->setItemNameProperty('name')
			->setPageTitle('Routes')
			->setPageSubtitle('Manage the routes of Extreme Way of the Cross')
			->setIndexPage('area_route_index')
			->setInfoPage('area_route_info')
			->setInsertPage('area_route_insert')
			->setEditPage('area_route_edit')
			->setRemovePage('area_route_remove')
			->setItemCreatedMessage('The route \'0\' has been created.')
			->setItemUpdatedMessage('The route \'0\' has been updated.')
			->setItemRemovedMessage('The route \'0\' has been removed.')
			->setRemoveQuestion('Do you really want to remove the route \'0\'?');

		$this->breadcrumbs()
			->workgroup('area')
			->entryLink($this->trans('Routes', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="area_route_index")
	 */
	public function indexAction(Request $request)
	{
		return $this->performIndex($request);
	}
	
	/**
	 * @Route("/ajax-list", name="area_route_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		return $this->performAjaxList($request);
	}
	
	/**
	 * @Route("/{id}/info", name="area_route_info")
	 */
	public function infoAction($id)
	{
		return $this->performInfo($id);
	}
	 
	/**
	 * @Route("/insert", name="area_route_insert")
	 */
	public function insertAction(Request $request)
	{
		return $this->performInsert($request);
	}
	
	/**
	 * @Route("/{id}/edit", name="area_route_edit")
	 */
	public function editAction($id, Request $request)
	{
		return $this->performEdit($id, $request);
	}
	
	/**
	 * @Route("/{id}/remove", name="area_route_remove")
	 */
	public function removeAction($id, Request $request)
	{
		return $this->performRemove($id, $request);
	}
	
	/**
	 * @Route("/{id}/ajax-reload", name="area_route_ajax_reload")
	 */
	public function ajaxReloadAction($id, Request $request)
	{
		return $this->performAjaxReload($id, $request);
	}
	
	/**
	 * @Route("/{id}/ajax-update", name="area_route_ajax_update")
	 */
	public function ajaxUpdateAction($id, Request $request)
	{
		return $this->performAjaxUpdate($id, $request);
	}
	
	/**
	 * @Route("/{id}/ajax-feed", name="area_route_ajax_feed")
	 */
	public function ajaxFeedAction($id, Request $request)
	{
		return $this->performAjaxFeed($id, $request);
	}
	
	/**
	 * @Route("/{id}/ajax-post", name="area_route_ajax_post")
	 */
	public function ajaxPostAction($id, Request $request)
	{
		return $this->performAjaxPost($id, $request);
	}
}
