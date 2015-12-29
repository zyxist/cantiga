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
namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\UserPageController;
use Cantiga\Metamodel\Exception\ModelException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/user/invitations")
 * @Security("has_role('ROLE_USER')")
 */
class UserInvitationController extends UserPageController
{
	const REPOSITORY_NAME = 'cantiga.core.repo.invitation';

	/**
	 * @Route("/index", name="user_invitation_index")
	 */
    public function indexAction(Request $request)
    {
		$repository = $this->get(self::REPOSITORY_NAME);
		$this->breadcrumbs()
			->workgroup('profile')
			->entryLink($this->trans('Invitations'), 'user_invitation_index');
        return $this->render('CantigaCoreBundle:UserInvitation:index.html.twig', array(
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
			
			return $this->showPageWithMessage('InvitationFoundText', 'user_invitation_index');
		} catch (ModelException $ex) {
			return $this->showPageWithError($this->trans($ex->getMessage()), 'user_invitation_index');
		}
	}
	
	/**
	 * @Route("/{id}/accept", name="user_invitation_accept")
	 */
	public function acceptAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$repository->accept($id, $this->getUser());
			
			return $this->showPageWithMessage('InvitationAcceptedText', 'user_invitation_index');
		} catch (ModelException $ex) {
			return $this->showPageWithError($this->trans($ex->getMessage()), 'user_invitation_index');
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
			
			return $this->showPageWithMessage('InvitationRevokedText', 'user_invitation_index');
		} catch (ModelException $ex) {
			return $this->showPageWithError($this->trans($ex->getMessage()), 'user_invitation_index');
		}
	}
}
