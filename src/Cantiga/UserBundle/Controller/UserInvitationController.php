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

use Cantiga\CoreBundle\Api\Controller\UserPageController;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\UserBundle\Entity\ContactData;
use Cantiga\UserBundle\Form\ContactDataForm;
use Cantiga\UserBundle\Repository\ContactRepository;
use Cantiga\UserBundle\Repository\InvitationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/user/invitations")
 * @Security("has_role('ROLE_USER')")
 */
class UserInvitationController extends UserPageController
{
	const TEMPLATE_LOCATION = 'CantigaUserBundle:Invitation';
	const REPOSITORY_NAME = 'cantiga.user.repo.invitation';

	/**
	 * @Route("/index", name="user_invitation_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$this->breadcrumbs()
			->entryLink($this->trans('Invitations', [], 'pages'), 'user_invitation_index');
		return $this->render(self::TEMPLATE_LOCATION.':index.html.twig', array(
				'invitations' => $repository->findInvitations($this->getUser()),
		));
	}

	/**
	 * @Route("/find", name="user_invitation_find")
	 */
	public function findAction(Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$repository->findAndJoin($request->get('invitationKey'), $this->getUser());

			return $this->showPageWithMessage($this->trans('InvitationFoundText', [], 'users'), 'user_invitation_index');
		} catch (ModelException $ex) {
			return $this->showPageWithError($this->trans($ex->getMessage(), [], 'users'), 'user_invitation_index');
		}
	}

	/**
	 * @Route("/{id}/accept", name="user_invitation_accept")
	 */
	public function acceptAction($id, Request $request)
	{
		try {
			$invitation = $this->getInvitationRepository()->getItem($id, $this->getUser());
			$project = $this->getContactRepository()->getPlaceProject($invitation->getPlace());
			$contactData = $this->getContactRepository()->findContactData($project, $this->getUser());
			
			$form = $this->createForm(
				ContactDataForm::class, $contactData, [
					'action' => $this->generateUrl('user_invitation_accept', ['id' => $id]),
				]
			);
			$form->handleRequest($request);
			if ($form->isValid()) {
				$this->getContactRepository()->persistContactData($contactData);
				$this->getInvitationRepository()->accept($id, $this->getUser());
				return $this->showPageWithMessage($this->trans('InvitationAcceptedText', [], 'users'), 'user_invitation_index');
			}
			
			$this->breadcrumbs()
				->entryLink($this->trans('Invitations', [], 'pages'), 'user_invitation_index')
				->link($this->trans('Accept invitation', [], 'pages'), 'user_invitation_accept', ['id' => $id]);
			return $this->render(self::TEMPLATE_LOCATION.':fill-contact-data.html.twig', [
				'form' => $form->createView(),
				'invitation' => $invitation,
				'project' => $project
			]);
		} catch (ModelException $ex) {
			return $this->showPageWithError($this->trans($ex->getMessage(), [], 'users'), 'user_invitation_index');
		}
	}

	/**
	 * @Route("/{id}/revoke", name="user_invitation_revoke")
	 */
	public function revokeAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$repository->revoke($id, $this->getUser());

			return $this->showPageWithMessage($this->trans('InvitationRevokedText', [], 'users'), 'user_invitation_index');
		} catch (ModelException $ex) {
			return $this->showPageWithError($this->trans($ex->getMessage(), [], 'users'), 'user_invitation_index');
		}
	}
	
	private function getInvitationRepository(): InvitationRepository
	{
		return $this->get(self::REPOSITORY_NAME);
	}
	
	private function getContactRepository(): ContactRepository
	{
		return $this->get('cantiga.user.repo.contact');
	}
}
