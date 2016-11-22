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

use Cantiga\CoreBundle\Api\Actions\FormAction;
use Cantiga\CoreBundle\Api\Controller\UserPageController;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\UserBundle\Form\UserChangeEmailForm;
use Cantiga\UserBundle\Form\UserChangePasswordForm;
use Cantiga\UserBundle\Form\UserPhotoUploadForm;
use Cantiga\UserBundle\Form\UserProfileForm;
use Cantiga\UserBundle\Form\UserSettingsForm;
use Cantiga\UserBundle\Intent\EmailChangeIntent;
use Cantiga\UserBundle\Intent\PasswordChangeIntent;
use Cantiga\UserBundle\Intent\UserProfilePhotoIntent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/user/profile")
 * @Security("has_role('ROLE_USER')")
 */
class ProfileController extends UserPageController
{
	const REPOSITORY = 'cantiga.user.repo.profile';
	const TEMPLATE_LOCATION = 'CantigaUserBundle:Profile';
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->breadcrumbs()->workgroup('profile');
	}
	
	/**
	 * @Route("/contact-data", name="user_profile_contact_data")
	 */
	public function contactDataAction(Request $request)
	{
		$this->breadcrumbs()->entryLink($this->trans('Contact data', [], 'pages'), 'user_profile_contact_data');
		return $this->render(self::TEMPLATE_LOCATION.':contact-data.html.twig');
	}
	
	/**
	 * @Route("/settings", name="user_profile_settings")
	 */
	public function settingsAction(Request $request)
	{
		$this->breadcrumbs()->entryLink($this->trans('Settings', [], 'pages'), 'user_profile_settings');
		$repo = $this->get(self::REPOSITORY);
		$action = new FormAction($this->getUser(), UserSettingsForm::class, ['languageRepository' => $this->get('cantiga.core.repo.language')]);
		return $action->action($this->generateUrl('user_profile_settings'))
			->template(self::TEMPLATE_LOCATION.':settings.html.twig')
			->redirect($this->generateUrl('user_profile_settings'))
			->formSubmittedMessage('UserSettingsUpdatedText')
			->onSubmit(function($user) use($repo) {
				$repo->updateSettings($user);
				
				$this->get('session')->set('timezone', $user->getSettingsTimezone());
				$this->get('session')->set('_locale', $user->getSettingsLanguage()->getLocale());
			})
			->run($this, $request);
	}
	
	/**
	 * @Route("/change-mail", name="user_profile_change_mail")
	 */
	public function changeEmailAction(Request $request)
	{
		$this->breadcrumbs()->entryLink($this->trans('Change e-mail', [], 'pages'), 'user_profile_change_mail');
		$repo = $this->get(self::REPOSITORY);
		$intent = new EmailChangeIntent($repo, $this->get('event_dispatcher'), $this->get('security.encoder_factory'), $this->getUser());
		$action = new FormAction($intent, UserChangeEmailForm::class);
		return $action->action($this->generateUrl('user_profile_change_mail'))
			->template(self::TEMPLATE_LOCATION.':change-email.html.twig')
			->redirect($this->generateUrl('user_profile_change_mail'))
			->formSubmittedMessage('ConfirmationLinkChangeEmailSentText')
			->onSubmit(function(EmailChangeIntent $intent) use($repo) {
				$intent->execute();
			})
			->run($this, $request);
	}
	
	/**
	 * @Route("/change-pass", name="user_profile_change_password")
	 */
	public function changePasswordAction(Request $request)
	{
		$this->breadcrumbs()->entryLink($this->trans('Change password', [], 'pages'), 'user_profile_change_password');
		$repo = $this->get(self::REPOSITORY);
		$intent = new PasswordChangeIntent($repo, $this->get('event_dispatcher'), $this->get('security.encoder_factory'), $this->getUser());
		$action = new FormAction($intent, UserChangePasswordForm::class);
		return $action->action($this->generateUrl('user_profile_change_password'))
			->template(self::TEMPLATE_LOCATION.':change-password.html.twig')
			->redirect($this->generateUrl('user_profile_change_password'))
			->formSubmittedMessage('ConfirmationLinkChangePasswordSentText')
			->onSubmit(function(PasswordChangeIntent $intent) use($repo) {
				$intent->execute();
			})
			->run($this, $request);
	}
	
	/**
	 * @Route("/confirm-credential-change/{id}/{provisionKey}", name="user_profile_confirm_credential_change")
	 */
	public function confirmChangeAction($id, $provisionKey, Request $request)
	{
		try {
			$repo = $this->get(self::REPOSITORY);
			$changeRequest = $repo->getCredentialChangeRequest($id, $this->getUser());
			if ($changeRequest->verify($provisionKey, $_SERVER['REMOTE_ADDR'])) {
				$changeRequest->export();
			}
			$repo->completeCredentialChangeRequest($changeRequest);
			$this->get('session')->getFlashBag()->add('info', $this->trans('The credentials have been changed.', [], 'users'));
		} catch(ModelException $exception) {
			$this->get('session')->getFlashBag()->add('error', $this->trans('An error occured during the credential update.', [], 'users'));
		}
		return $this->redirect($this->generateUrl('cantiga_home_page'));
	}
	
	/**
	 * @Route("/manage-photo", name="user_profile_photo")
	 */
	public function photoAction(Request $request)
	{
		$this->breadcrumbs()->entryLink($this->trans('Manage photo', [], 'pages'), 'user_profile_photo');
		$repo = $this->get(self::REPOSITORY);
		$intent = new UserProfilePhotoIntent($this->getUser(), $repo, $this->get('kernel')->getRootDir().'/../web/ph');
		$action = new FormAction($intent, UserPhotoUploadForm::class);
		return $action->action($this->generateUrl('user_profile_photo'))
			->template(self::TEMPLATE_LOCATION.':photo.html.twig')
			->redirect($this->generateUrl('user_profile_photo'))
			->formSubmittedMessage('UserPhotoUploadedText')
			->onSubmit(function(UserProfilePhotoIntent $intent) use($repo) {
				$intent->execute();
			})
			->run($this, $request);
	}
}
