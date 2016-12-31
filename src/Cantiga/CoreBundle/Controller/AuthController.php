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

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\CoreTexts;
use Cantiga\CoreBundle\Entity\Intent\PasswordRecoveryCompleteIntent;
use Cantiga\CoreBundle\Entity\Intent\PasswordRecoveryRequestIntent;
use Cantiga\CoreBundle\Entity\Intent\UserRegistrationIntent;
use Cantiga\CoreBundle\Entity\PasswordRecoveryRequest;
use Cantiga\CoreBundle\Exception\PasswordRecoveryException;
use Cantiga\CoreBundle\Form\PasswordRecoveryCompleteForm;
use Cantiga\CoreBundle\Form\PasswordRecoveryRequestForm;
use Cantiga\CoreBundle\Form\UserRegistrationForm;
use Cantiga\Metamodel\Exception\ModelException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

class AuthController extends CantigaController
{
	const AUTH_CHECK_ROUTE = 'cantiga_auth_check';
	const TEMPLATE_NAME = 'CantigaCoreBundle:Auth:login.html.twig';
	const SUCCESSFUL_REGISTRATION_TEXT = 'Your account registration request has been created. Please check your e-mail to get the activation link.';

	public function loginAction(Request $request)
	{
		$session = $request->getSession();
		
		$text = $this->getTextRepository()->getText(CoreTexts::LOGIN_TEXT, $request);
		$languages = $this->get('cantiga.core.repo.language')->getLanguageCodes();

		if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
			$error = $request->attributes->get(
				Security::AUTHENTICATION_ERROR
			);
		} elseif (null !== $session && $session->has(Security::AUTHENTICATION_ERROR)) {
			$error = $session->get(Security::AUTHENTICATION_ERROR);
			$session->remove(Security::AUTHENTICATION_ERROR);
		} else {
			$error = '';
		}
		$lastUsername = (null === $session) ? '' : $session->get(Security::LAST_USERNAME);

		return $this->render(self::TEMPLATE_NAME, [
			'last_username' => $lastUsername,
			'authCheckRoute' => self::AUTH_CHECK_ROUTE,
			'error' => $error,
			'text' => $text,
			'languages' => $languages
		]);
	}
	
	public function routeAction(Request $request)
	{
		if ($user->getAfterLogin() == null) {
			return $this->redirect($this->generateUrl('cantiga_home_page'));
		} else {
			return $this->redirect($user->getAfterLogin());
		}
	}
	
	public function registerAction(Request $request)
	{
		$repository = $this->get('cantiga.core.repo.user_registration');
		$langRepo = $this->get('cantiga.core.repo.language');
		try {
			$intent = new UserRegistrationIntent($repository);
			$intent->email = $request->get('fromMail', '');

			$form = $this->createForm(UserRegistrationForm::class, $intent, [
				'action' => $this->generateUrl('cantiga_auth_register'),
				'languageRepository' => $langRepo
			]);
			$form->handleRequest($request);

			if ($form->isValid()) {
				$intent->execute();			
				return $this->render('CantigaCoreBundle:Auth:post-registration.html.twig');
			}
			return $this->render('CantigaCoreBundle:Auth:register.html.twig', [
				'item' => $intent,
				'form' => $form->createView(),
			]);
		} catch(ModelException $exception) {
			$this->get('session')->getFlashBag()->add('error', $this->trans($exception->getMessage()));
			return $this->redirect($this->generateUrl('cantiga_auth_register', [], RouterInterface::ABSOLUTE_URL));
		}
	}
	
	public function activateAccountAction($id, $provisionKey, Request $request)
	{
		$repository = $this->get('cantiga.core.repo.user_registration');
		try {
			$repository->activate($id, $provisionKey);
			return $this->render('CantigaCoreBundle:Auth:activated.html.twig');
		} catch (ModelException $ex) {
			return $this->render('CantigaCoreBundle:Auth:activation-failure.html.twig');
		}
	}
	
	public function passwordRecoveryAction(Request $request)
	{
		$repository = $this->get('cantiga.user_provider');
		$item = new PasswordRecoveryRequestIntent($repository, $this->get('event_dispatcher'));

		$form = $this->createForm(PasswordRecoveryRequestForm::class, $item, ['action' => $this->generateUrl('cantiga_auth_recovery')]);
		$form->handleRequest($request);
		if ($form->isValid()) {
			try {
				$item->execute();			
				return $this->render('CantigaCoreBundle:Auth:password-recovery-sent.html.twig');
			} catch(PasswordRecoveryException $exception) {
				$this->get('session')->getFlashBag()->add('error', $this->trans($exception->getMessage()));
			}
		}
		return $this->render('CantigaCoreBundle:Auth:password-recovery.html.twig', [
			'item' => $item,
			'form' => $form->createView(),
		]);
	}
	
	public function passwordRecoveryCompleteAction($id, $provisionKey, Request $request)
	{
		try {
			$repository = $this->get('cantiga.user_provider');
			$pcRequest = $repository->getPasswordRecoveryRequest($id);
			$pcRequest->verify($provisionKey, $_SERVER['REMOTE_ADDR']);
			
			if($pcRequest->getStatus() == PasswordRecoveryRequest::STATUS_OK) {
				$intent = new PasswordRecoveryCompleteIntent($repository, $this->get('event_dispatcher'), $this->get('security.encoder_factory'));
				$form = $this->createForm(PasswordRecoveryCompleteForm::class, $intent, ['action' => $this->generateUrl('cantiga_auth_recovery_complete', ['id' => $id, 'provisionKey' => $provisionKey])]);
				$form->handleRequest($request);
				if ($form->isValid()) {
					try {
						$intent->execute($pcRequest);			
						return $this->render('CantigaCoreBundle:Auth:reset-password-success.html.twig');
					} catch(PasswordRecoveryException $exception) {
						$this->get('session')->getFlashBag()->add('error', $this->trans($exception->getMessage()));
					}
				}
				return $this->render('CantigaCoreBundle:Auth:reset-password.html.twig', [
					'item' => $intent,
					'form' => $form->createView(),
				]);
			} else {
				return $this->render('CantigaCoreBundle:Auth:reset-password-failure.html.twig');
			}
		} catch(PasswordRecoveryException $exception) {
			return $this->render('CantigaCoreBundle:Auth:reset-password-failure.html.twig');
		}
	}
	
	public function termsAction(Request $request)
	{
		$text = $this->getTextRepository()->getText(CoreTexts::TERMS_OF_USE_TEXT, $request);
		return $this->render('CantigaCoreBundle:Auth:terms-of-use.html.twig', ['text' => $text]);
	}
}
