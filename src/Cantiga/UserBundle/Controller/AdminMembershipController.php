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
namespace Cantiga\UserBundle\Controller;

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\CoreBundle\Api\Controller\AdminPageController;
use Cantiga\CoreBundle\Entity\Project;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/admin/project/{id}/membership")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminMembershipController extends AdminPageController
{
	use MembershipControllerTrait;
	
	const INDEX_PAGE = 'admin_membership_index';
	const INVITATION_PAGE = 'admin_membership_invite';
	const HINTS_PAGE = 'admin_membership_api_hints';
	const RELOAD_PAGE = 'admin_membership_api_reload';
	const ADD_PAGE = 'admin_membership_api_add';
	const EDIT_PAGE = 'admin_membership_api_edit';
	const REMOVE_PAGE = 'admin_membership_api_remove';
	const TEMPLATE_LOCATION = 'CantigaUserBundle:Membership';
	const REPOSITORY = 'cantiga.user.repo.membership';

	private $managedPlace;
	private $placeType;
	private $id;
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->id = $request->attributes->get('id');
		$this->managedPlace = $this->findManagedProject($this->id);

		$this->breadcrumbs()
			->workgroup('projects')
			->entryLink($this->trans($this->managedPlace->getTypeName().'s', [], 'pages'), 'admin_project_index', [])
			->link($this->managedPlace->getName(), 'admin_project_info', ['id' => $this->managedPlace->getId()])
			->link($this->trans($this->managedPlace->getTypeName().' members', [], 'pages'), 'admin_membership_index', ['id' => $this->id]);
	}
	
	/**
	 * @Route("/index", name="admin_membership_index")
	 */
	public function indexAction(Request $request)
	{
		return $this->index($request);
	}

	/**
	 * @Route("/api/hints", name="admin_membership_api_hints")
	 */
	public function apiHintsAction(Request $request)
	{
		return $this->apiHints($request);
	}
	
	/**
	 * @Route("/api/reload", name="admin_membership_api_reload")
	 */
	public function apiReloadAction(Request $request)
	{
		return $this->apiReload($request);
	}
	
	/**
	 * @Route("/api/edit", name="admin_membership_api_edit")
	 */
	public function apiEditAction(Request $request)
	{
		return $this->apiEdit($request);
	}
	
	/**
	 * @Route("/api/remove", name="admin_membership_api_remove")
	 */
	public function apiRemoveAction(Request $request)
	{
		return $this->apiRemove($request);
	}

	/**
	 * @Route("/invite", name="admin_membership_invite")
	 */
	public function inviteAction(Request $request)
	{
		return $this->invite($request);
	}
	
	protected function getExtraArgs(): array
	{
		return ['id' => $this->id];
	}

	protected function getManagedPlace(): HierarchicalInterface
	{
		return $this->managedPlace;
	}
	
	private function findManagedProject($id): Project
	{
		return $this->get('cantiga.core.repo.place.project')->loadPlaceById($id);
	}
}
