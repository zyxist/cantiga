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
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/s/{slug}/{placeType}/{id}/membership")
 * @Security("is_granted('PLACE_MEMBER') and (is_granted('MEMBEROF_PROJECT') or is_granted('MEMBEROF_GROUP'))")
 */
class DownstreamMembershipController extends WorkspaceController
{
	use MembershipControllerTrait;
	
	const INDEX_PAGE = 'downstream_membership_index';
	const INVITATION_PAGE = 'downstream_membership_invite';
	const HINTS_PAGE = 'downstream_membership_api_hints';
	const RELOAD_PAGE = 'downstream_membership_api_reload';
	const ADD_PAGE = 'downstream_membership_api_add';
	const EDIT_PAGE = 'downstream_membership_api_edit';
	const REMOVE_PAGE = 'downstream_membership_api_remove';
	const TEMPLATE_LOCATION = 'CantigaUserBundle:Membership';
	const REPOSITORY = 'cantiga.user.repo.membership';

	private $managedPlace;
	private $placeType;
	private $id;
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$currentPlace = $this->get('cantiga.user.membership.storage')->getMembership()->getPlace();
		$this->placeType = $request->attributes->get('placeType');
		$this->id = $request->attributes->get('id');
		$this->managedPlace = $this->findManagedPlace($currentPlace, $this->placeType, $this->id);

		$this->breadcrumbs()
			->workgroup('data')
			->entryLink($this->trans($this->managedPlace->getTypeName().'s', [], 'pages'), $this->placeType.'_mgmt_index', ['slug' => $this->getSlug()])
			->link($this->managedPlace->getName(), $this->placeType.'_mgmt_info', ['slug' => $this->getSlug(), 'id' => $this->managedPlace->getId()])
			->link($this->trans($this->managedPlace->getTypeName().' members', [], 'pages'), 'downstream_membership_index', ['slug' => $this->getSlug(), 'placeType' => $this->placeType, 'id' => $this->id]);
	}

	/**
	 * @Route("/index", name="downstream_membership_index")
	 */
	public function indexAction(Request $request)
	{
		return $this->index($request);
	}

	/**
	 * @Route("/api/hints", name="downstream_membership_api_hints")
	 */
	public function apiHintsAction(Request $request)
	{
		return $this->apiHints($request);
	}
	
	/**
	 * @Route("/api/reload", name="downstream_membership_api_reload")
	 */
	public function apiReloadAction(Request $request)
	{
		return $this->apiReload($request);
	}
	
	/**
	 * @Route("/api/edit", name="downstream_membership_api_edit")
	 */
	public function apiEditAction(Request $request)
	{
		return $this->apiEdit($request);
	}
	
	/**
	 * @Route("/api/remove", name="downstream_membership_api_remove")
	 */
	public function apiRemoveAction(Request $request)
	{
		return $this->apiRemove($request);
	}

	/**
	 * @Route("/invite", name="downstream_membership_invite")
	 */
	public function inviteAction(Request $request)
	{
		return $this->invite($request);
	}
	
	protected function getExtraArgs(): array
	{
		return ['placeType' => $this->placeType, 'id' => $this->id];
	}

	protected function getManagedPlace(): HierarchicalInterface
	{
		return $this->managedPlace;
	}
	
	private function findManagedPlace(HierarchicalInterface $currentPlace, string $placeType, int $id)
	{
		if ($placeType == 'project' || $placeType == 'group' || $placeType == 'area') {
			try {
				$managedPlace = $this->get('cantiga.core.repo.place.'.$placeType)->loadPlaceById($id);
				if ($currentPlace->isChild($managedPlace)) {
					return $managedPlace;
				}
			} catch (ItemNotFoundException $exception) {
				throw new AccessDeniedException('MembershipManagementNotAllowedInThisPlace');
			}
		}
		throw new AccessDeniedException('MembershipManagementNotAllowedInThisPlace');
	}
	
}
