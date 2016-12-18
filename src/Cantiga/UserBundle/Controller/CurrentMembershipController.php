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

use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\CoreBundle\Api\Controller\WorkspaceController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/s/{slug}/membership")
 * @Security("is_granted('PLACE_MANAGER') and is_granted('MEMBEROF_ANY')")
 */
class CurrentMembershipController extends WorkspaceController
{
	use MembershipControllerTrait;
	
	const INDEX_PAGE = 'current_membership_index';
	const INVITATION_PAGE = 'current_membership_invite';
	const HINTS_PAGE = 'current_membership_api_hints';
	const RELOAD_PAGE = 'current_membership_api_reload';
	const ADD_PAGE = 'current_membership_api_add';
	const EDIT_PAGE = 'current_membership_api_edit';
	const REMOVE_PAGE = 'current_membership_api_remove';
	const TEMPLATE_LOCATION = 'CantigaUserBundle:Membership';
	const REPOSITORY = 'cantiga.user.repo.membership';

	private $managedPlace;
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->managedPlace = $this->get('cantiga.user.membership.storage')->getMembership()->getPlace();
		$this->breadcrumbs()
			->workgroup('manage')
			->entryLink($this->trans($this->managedPlace->getTypeName().' members', [], 'pages'), self::INDEX_PAGE, ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="current_membership_index")
	 */
	public function indexAction(Request $request)
	{
		return $this->index($request);
	}

	/**
	 * @Route("/api/hints", name="current_membership_api_hints")
	 */
	public function apiHintsAction(Request $request)
	{
		return $this->apiHints($request);
	}
	
	/**
	 * @Route("/api/reload", name="current_membership_api_reload")
	 */
	public function apiReloadAction(Request $request)
	{
		return $this->apiReload($request);
	}

	/**
	 * @Route("/api/edit", name="current_membership_api_edit")
	 */
	public function apiEditAction(Request $request)
	{
		return $this->apiEdit($request);
	}
	
	/**
	 * @Route("/api/remove", name="current_membership_api_remove")
	 */
	public function apiRemoveAction(Request $request)
	{
		return $this->apiRemove($request);
	}

	/**
	 * @Route("/invite", name="current_membership_invite")
	 */
	public function inviteAction(Request $request)
	{
		return $this->invite($request);
	}

	protected function getExtraArgs(): array
	{
		return [];
	}

	protected function getManagedPlace(): HierarchicalInterface
	{
		return $this->managedPlace;
	}
}
