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
namespace WIO\EdkBundle\Controller;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/routes")
 * @Security("has_role('ROLE_PROJECT_MEMBER')")
 */
class ProjectRouteController extends ProjectPageController
{
	use Traits\RouteTrait;
	
	const REPOSITORY_NAME = 'wio.edk.repo.route';
	const AJAX_LIST_PAGE = 'project_route_ajax_list';
	const AJAX_RELOAD_PAGE = 'project_route_ajax_reload';
	const AJAX_UPDATE_PAGE = 'project_route_ajax_update';
	const AJAX_FEED_PAGE = 'project_route_ajax_feed';
	const AJAX_POST_PAGE = 'project_route_ajax_post';
	const APPROVE_PAGE = 'project_route_approve';
	const REVOKE_PAGE = 'project_route_revoke';
	
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
			->setIndexPage('project_route_index')
			->setInfoPage('project_route_info')
			->setInsertPage('project_route_insert')
			->setEditPage('project_route_edit')
			->setRemovePage('project_route_remove')
			->setItemCreatedMessage('The route \'0\' has been created.')
			->setItemUpdatedMessage('The route \'0\' has been updated.')
			->setItemRemovedMessage('The route \'0\' has been removed.')
			->setRemoveQuestion('Do you really want to remove the route \'0\'?');

		$this->breadcrumbs()
			->workgroup('data')
			->entryLink($this->trans('Routes', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="project_route_index")
	 */
	public function indexAction(Request $request)
	{
		return $this->performIndex($request);
	}
	
	/**
	 * @Route("/ajax-list", name="project_route_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		return $this->performAjaxList($request);
	}
	
	/**
	 * @Route("/{id}/info", name="project_route_info")
	 */
	public function infoAction($id)
	{
		return $this->performInfo($id);
	}
	 
	/**
	 * @Route("/insert", name="project_route_insert")
	 */
	public function insertAction(Request $request)
	{
		return $this->performInsert($request);
	}
	
	/**
	 * @Route("/{id}/edit", name="project_route_edit")
	 */
	public function editAction($id, Request $request)
	{
		return $this->performEdit($id, $request);
	}
	
	/**
	 * @Route("/{id}/remove", name="project_route_remove")
	 */
	public function removeAction($id, Request $request)
	{
		return $this->performRemove($id, $request);
	}
	
	/**
	 * @Route("/{id}/ajax-reload", name="project_route_ajax_reload")
	 */
	public function ajaxReloadAction($id, Request $request)
	{
		return $this->performAjaxReload($id, $request);
	}
	
	/**
	 * @Route("/{id}/ajax-update", name="project_route_ajax_update")
	 */
	public function ajaxUpdateAction($id, Request $request)
	{
		return $this->performAjaxUpdate($id, $request);
	}
	
	/**
	 * @Route("/{id}/ajax-feed", name="project_route_ajax_feed")
	 */
	public function ajaxFeedAction($id, Request $request)
	{
		return $this->performAjaxFeed($id, $request);
	}
	
	/**
	 * @Route("/{id}/ajax-post", name="project_route_ajax_post")
	 */
	public function ajaxPostAction($id, Request $request)
	{
		return $this->performAjaxPost($id, $request);
	}
	
	/**
	 * @Route("/{id}/approve", name="project_route_approve")
	 */
	public function approveAction($id, Request $request)
	{
		return $this->performApprove($id, $request);
	}
	
	/**
	 * @Route("/{id}/revoke", name="project_route_revoke")
	 */
	public function revokeAction($id, Request $request)
	{
		return $this->performRevoke($id, $request);
	}
}
