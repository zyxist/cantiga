<?php
namespace Cantiga\CoreBundle\Entity\Intent;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\CoreBundle\Event\PasswordRecoveryEvent;
use Cantiga\CoreBundle\Repository\AuthRepository;

/**
 * @author Tomasz Jędrzejewski
 */
class PasswordRecoveryRequestIntent
{
	private $repository;
	private $dispatcher;
	
	public $login;
	public $email;
	
	public function __construct(AuthRepository $repository, EventDispatcherInterface $dispatcher)
	{
		$this->repository = $repository;
		$this->dispatcher = $dispatcher;
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata) {
		$metadata->addPropertyConstraint('login', new NotBlank());
		$metadata->addPropertyConstraint('login', new Length(array('min' => 6, 'max' => 40)));
		$metadata->addPropertyConstraint('email', new Length(array('min' => 2, 'max' => 100)));
		$metadata->addPropertyConstraint('email', new Email());
	}
	
	public function execute()
	{
		$user = $this->repository->findUserByNameMail($this->login, $this->email);
		if (null !== $user) {
			$request = $this->repository->createPasswordRecoveryRequest($user, $_SERVER['REMOTE_ADDR']);
			$this->dispatcher->dispatch(CantigaEvents::USER_PASSWORD_RECOVERY, new PasswordRecoveryEvent($request));
		}
	}
}
