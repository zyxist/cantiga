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
use Cantiga\CoreBundle\Entity\Invitation;
use Cantiga\CoreBundle\Form\InvitationForm;
use Cantiga\Metamodel\Exception\ModelException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

trait MembershipControllerTrait
{
	public function index(Request $request)
	{
		$place = $this->getManagedPlace();
		$roleResolver = $this->get('cantiga.roles');
		return $this->render(static::TEMPLATE_LOCATION.':index.html.twig', array(
			'place' => $place,
			'placeType' => $place->getTypeName(),
			'invitationPage' => static::INVITATION_PAGE,
			'hintsPage' => static::HINTS_PAGE,
			'reloadPage'=> static::RELOAD_PAGE,
			'editPage' => static::EDIT_PAGE,
			'removePage' => static::REMOVE_PAGE,
			'roles' => $roleResolver->getRoles($place->getTypeName()),
			'showContactsManageable' => (int) $this->isShowContactsManageable($place),
			'extraArgs' => $this->getExtraArgs()
		));
	}

	public function apiHints(Request $request)
	{
		try {
			$repository = $this->get(static::REPOSITORY);
			return new JsonResponse($repository->findHints($this->getManagedPlace()->getPlace(), $request->get('q')));
		} catch (Exception $ex) {
			return new JsonResponse([]);
		}
	}

	public function apiReload(Request $request)
	{
		try {
			$repository = $this->get(static::REPOSITORY);
			return new JsonResponse($repository->findMembers($this->getManagedPlace()->getPlace()));
		} catch (Exception $ex) {
			return new JsonResponse([]);
		}
	}

	public function apiEdit(Request $request)
	{
		$repository = $this->get(static::REPOSITORY);
		try {
			$managedPlace = $this->getManagedPlace();
			$member = $repository->getMember($managedPlace->getPlace(), $request->get('u'));
			$role = $repository->getRole($managedPlace->getPlace(), $request->get('r'));
			$note = $request->get('n');
			$showDownstreamContactData = (bool) $request->get('c');
			
			return new JsonResponse($repository->editMember($managedPlace->getPlace(), $member, $role, $note, $showDownstreamContactData));
		} catch(Exception $exception) {
			return ['status' => 0, 'error' => $exception->getMessage()];
		}
	}
	
	public function apiRemove(Request $request)
	{
		try {
			$managedPlace = $this->getManagedPlace();
			$repository = $this->get(static::REPOSITORY);
			$member = $repository->getMember($managedPlace->getPlace(), $request->get('u'));
			return new JsonResponse($repository->removeMember($managedPlace->getPlace(), $member));
		} catch(Exception $exception) {
			return ['status' => 0, 'error' => $exception->getMessage()];
		}
	}

	public function invite(Request $request)
	{
		$args = $this->getExtraArgs();
		if ($this instanceof WorkspaceController) {
			$args['slug'] = $this->getSlug();
		}
		try {			
			$managedPlace = $this->getManagedPlace();
			$roleResolver = $this->get('cantiga.roles');
			$repository = $this->get('cantiga.user.repo.invitation');
			$invitation = new Invitation();
			
			$form = $this->createForm(InvitationForm::class, $invitation, [
				'action' => $this->generateUrl(static::INVITATION_PAGE, $args),
				'roles' => $roleResolver->getRoles($managedPlace->getTypeName()),
				'showContactsManageable' => $this->isShowContactsManageable($managedPlace),
				'showContactHelpText' => 'AccessOthersContactsFrom'.$managedPlace->getTypeName().'HintText'
			]);
			$form->handleRequest($request);
			if ($form->isValid()) {
				$invitation->setInviter($this->getUser());
				$invitation->setPlace($managedPlace->getPlace());
				
				$repository->invite($invitation);
				return $this->showPageWithMessage($this->trans('The invitation has been sent.', [], 'users'), static::INDEX_PAGE, $args);
			}
			$this->breadcrumbs()->link($this->trans('Invite', [], 'general'), static::INVITATION_PAGE, $args);
			return $this->render(static::TEMPLATE_LOCATION.':invite.html.twig', [
				'place' => $managedPlace->getPlace(),
				'placeType' => $managedPlace->getTypeName(),
				'form' => $form->createView(),
				'showContactsManageable' => $this->isShowContactsManageable($managedPlace)
			]);
		} catch (ModelException $ex) {
			return $this->showPageWithError($this->trans($ex->getMessage()), static::INDEX_PAGE, $args);
		}
	}
	
	abstract protected function getExtraArgs(): array;
	
	abstract protected function getManagedPlace(): HierarchicalInterface;
	
	private function isShowContactsManageable(HierarchicalInterface $place): bool
	{
		return ($place->getTypeName() != 'Area');
	}
}
