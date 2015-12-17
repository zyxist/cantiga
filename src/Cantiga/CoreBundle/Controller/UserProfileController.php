<?php
namespace Cantiga\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Cantiga\CoreBundle\Api\Actions\FormAction;
use Cantiga\CoreBundle\Api\Controller\UserPageController;
use Cantiga\CoreBundle\Entity\Intent\EmailChangeIntent;
use Cantiga\CoreBundle\Entity\Intent\PasswordChangeIntent;
use Cantiga\CoreBundle\Entity\Intent\UserProfilePhotoIntent;
use Cantiga\CoreBundle\Form\UserChangeEmailForm;
use Cantiga\CoreBundle\Form\UserChangePasswordForm;
use Cantiga\CoreBundle\Form\UserPhotoUploadForm;
use Cantiga\CoreBundle\Form\UserProfileForm;
use Cantiga\CoreBundle\Form\UserSettingsForm;
use Cantiga\Metamodel\Exception\ModelException;

/**
 * @Route("/user/profile")
 * @Security("has_role('ROLE_USER')")
 */
class UserProfileController extends UserPageController
{
	const REPOSITORY = 'cantiga.core.repo.profile';
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->breadcrumbs()->workgroup('profile');
	}
	
	/**
	 * @Route("/personal-info", name="user_profile_personal_info")
	 */
	public function personalInfoAction(Request $request)
	{
		$this->breadcrumbs()->entryLink($this->trans('Personal information', [], 'pages'), 'user_profile_personal_info');
		$repo = $this->get(self::REPOSITORY);
		$action = new FormAction($this->getUser(), new UserProfileForm());
		return $action->action($this->generateUrl('user_profile_personal_info'))
			->template('CantigaCoreBundle:UserProfile:personal-information.html.twig')
			->redirect($this->generateUrl('user_profile_personal_info'))
			->formSubmittedMessage('Your profile has been updated.')
			->onSubmit(function($user) use($repo) {
				$repo->updateProfile($user);
			})
			->run($this, $request);
	}
	
	/**
	 * @Route("/settings", name="user_profile_settings")
	 */
	public function settingsAction(Request $request)
	{
		$this->breadcrumbs()->entryLink($this->trans('Settings', [], 'pages'), 'user_profile_settings');
		$repo = $this->get(self::REPOSITORY);
		$action = new FormAction($this->getUser(), new UserSettingsForm($this->get('cantiga.core.repo.language')));
		return $action->action($this->generateUrl('user_profile_settings'))
			->template('CantigaCoreBundle:UserProfile:settings.html.twig')
			->redirect($this->generateUrl('user_profile_settings'))
			->formSubmittedMessage('Your settings have been updated.')
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
		$action = new FormAction($intent, new UserChangeEmailForm());
		return $action->action($this->generateUrl('user_profile_change_mail'))
			->template('CantigaCoreBundle:UserProfile:change-email.html.twig')
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
		$action = new FormAction($intent, new UserChangePasswordForm());
		return $action->action($this->generateUrl('user_profile_change_password'))
			->template('CantigaCoreBundle:UserProfile:change-password.html.twig')
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
			$this->get('session')->getFlashBag()->add('info', $this->trans('The credentials have been changed.'));
		} catch(ModelException $exception) {
			$this->get('session')->getFlashBag()->add('error', $this->trans('An error occured during the credential update.'));
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
		$action = new FormAction($intent, new UserPhotoUploadForm());
		return $action->action($this->generateUrl('user_profile_photo'))
			->template('CantigaCoreBundle:UserProfile:photo.html.twig')
			->redirect($this->generateUrl('user_profile_photo'))
			->formSubmittedMessage('The new image has been set up.')
			->onSubmit(function(UserProfilePhotoIntent $intent) use($repo) {
				$intent->execute();
			})
			->run($this, $request);
	}
}
