<?php
namespace Cantiga\CoreBundle\EventListener;

use Cantiga\CoreBundle\CoreTexts;
use Cantiga\CoreBundle\Event\AreaRequestApprovedEvent;
use Cantiga\CoreBundle\Event\AreaRequestEvent;
use Cantiga\CoreBundle\Event\CredentialChangeEvent;
use Cantiga\CoreBundle\Event\InvitationEvent;
use Cantiga\CoreBundle\Event\PasswordRecoveryEvent;
use Cantiga\CoreBundle\Event\UserRegistrationEvent;
use Cantiga\CoreBundle\Mail\MailSenderInterface;

/**
 * Handles sending the basic mail messages related to the account management,
 * invitations, etc.
 *
 * @author Tomasz Jędrzejewski
 */
class MailingListener
{
	/**
	 * @var MailSenderInterface 
	 */
	private $mailSender;
	
	public function __construct(MailSenderInterface $mailSender)
	{
		$this->mailSender = $mailSender;
	}
	
	public function onUserRegistration(UserRegistrationEvent $event)
	{
		$reg = $event->getRegistration();
		$this->mailSender->send(
			CoreTexts::USER_REGISTRATION_MAIL,
			$reg->getEmail(),
			'user \''.$reg->getLogin().'\'',
			[
				'id' => $reg->getId(),
				'name' => $reg->getName(),
				'login' => $reg->getLogin(),
				'key' => $reg->getProvisionKey()
			]
		);
	}
	
	public function onPasswordRecovery(PasswordRecoveryEvent $event)
	{
		
		$request = $event->getRequest();
		$this->mailSender->send(
			CoreTexts::PASSWORD_RECOVERY_MAIL,
			$request->getUser()->getEmail(),
			'password recovery request for user \''.$request->getUser()->getLogin().'\' from IP '.long2ip($request->getRequestIp()),
			[
				'login' => $request->getUser()->getLogin(),
				'id' => $request->getId(),
				'key' => $request->getProvisionKey(),
				'ip' => long2ip($request->getRequestIp()),
				'time' => $request->getRequestTime()
			]
		);
	}
	
	public function onPasswordRecoveryCompleted(PasswordRecoveryEvent $event)
	{
		$request = $event->getRequest();
		$this->mailSender->send(
			CoreTexts::PASSWORD_RECOVERY_COMPLETED_MAIL,
			$request->getUser()->getEmail(),
			'password recovery completed for user \''.$request->getUser()->getLogin().'\' from IP '.long2ip($request->getRequestIp()),
			[ 'login' => $request->getUser()->getLogin() ]
		);
	}
	
	public function onCredentialChange(CredentialChangeEvent $event)
	{
		$request = $event->getChangeRequest();
		$this->mailSender->send(
			CoreTexts::CREDENTIAL_CHANGE_MAIL,
			$request->getUser()->getEmail(),
			'credential change for user \''.$request->getUser()->getLogin().'\' from IP '.long2ip($request->getRequestIp()),
			[ 'login' => $request->getUser()->getLogin(), 'id' => $request->getId(), 'key' => $request->getProvisionKey() ]
		);
	}
	
	public function onInvitation(InvitationEvent $event)
	{
		$invitation = $event->getInvitation();
		
		if ($invitation->getUser() !== null) {
			$this->mailSender->send(
				CoreTexts::INVITATION_MEMBER_MAIL,
				$invitation->getEmail(),
				'invitation sent to \''.$invitation->getEmail().'\' (member: \''.$invitation->getUser()->getName().'\') by '.$invitation->getInviter()->getName(),
				[ 'invitation' => $invitation, 'inviter' => $invitation->getInviter(), 'user' => $invitation->getUser() ]
			);
		} else {
			$this->mailSender->send(
				CoreTexts::INVITATION_ANONYMOUS_MAIL,
				$invitation->getEmail(),
				'anonymous invitation sent to \''.$invitation->getEmail().'\' by '.$invitation->getInviter()->getName(),
				[ 'invitation' => $invitation, 'inviter' => $invitation->getInviter() ]
			);
		}
	}
	
	public function onAreaRequestCreated(AreaRequestEvent $event)
	{
		$request = $event->getAreaRequest();
		$this->mailSender->send(
			CoreTexts::AREA_REQUEST_CREATED_MAIL,
			$request->getRequestor()->getEmail(),
			'notification about creating area request \''.$request->getName().'\' sent to \''.$request->getRequestor()->getEmail().'\'',
			[ 'request' => $request ]
		);
	}
	
	public function onAreaRequestVerification(AreaRequestEvent $event)
	{
		$request = $event->getAreaRequest();
		$this->mailSender->send(
			CoreTexts::AREA_REQUEST_VERIFICATION_MAIL,
			$request->getRequestor()->getEmail(),
			'notification about area request \''.$request->getName().'\' verification sent to \''.$request->getRequestor()->getEmail().'\'',
			[ 'request' => $request ]
		);
	}
	
	public function onAreaRequestRevoked(AreaRequestEvent $event)
	{
		$request = $event->getAreaRequest();
		$this->mailSender->send(
			CoreTexts::AREA_REQUEST_REVOKED_MAIL,
			$request->getRequestor()->getEmail(),
			'notification about area request \''.$request->getName().'\' revoke sent to \''.$request->getRequestor()->getEmail().'\'',
			[ 'request' => $request ]
		);
	}
	
	public function onAreaRequestApproved(AreaRequestApprovedEvent $event)
	{
		$request = $event->getAreaRequest();
		$this->mailSender->send(
			CoreTexts::AREA_REQUEST_APPROVED_MAIL,
			$request->getRequestor()->getEmail(),
			'notification about area request \''.$request->getName().'\' approval sent to \''.$request->getRequestor()->getEmail().'\'',
			[ 'request' => $request, 'area' => $event->getArea() ]
		);
	}
}
