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
namespace Cantiga\UserBundle\Intent;

use Cantiga\CoreBundle\Entity\CredentialChangeRequest;
use Cantiga\CoreBundle\Entity\PasswordBuilder;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\CoreBundle\Event\CredentialChangeEvent;
use Cantiga\UserBundle\Repository\ProfileRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class PasswordChangeIntent
{
	/**
	 * @var ProfileRepository
	 */
	private $repository;
	/**
	 * @var EventDispatcherInterface 
	 */
	private $dispatcher;
	/**
	 * @var EncoderFactoryInterface 
	 */
	private $encoderFactory;
	/**
	 * @var User 
	 */
	private $user;
	
	public $oldPassword;
	public $password;
	public $repeatPassword;
	
	public function __construct(ProfileRepository $repository, EventDispatcherInterface $dispatcher, EncoderFactoryInterface $encoderFactory, User $user)
	{
		$this->repository = $repository;
		$this->dispatcher = $dispatcher;
		$this->encoderFactory = $encoderFactory;
		$this->user = $user;
	}

	public static function loadValidatorMetadata(ClassMetadata $metadata) {
		$metadata->addConstraint(new Callback('checkPassword'));
		
		$metadata->addPropertyConstraint('oldPassword', new NotBlank());
		$metadata->addPropertyConstraint('oldPassword', new Length(array('min' => 8, 'max' => 40)));
		$metadata->addPropertyConstraint('password', new NotBlank());
		$metadata->addPropertyConstraint('password', new Length(array('min' => 8, 'max' => 40)));
		$metadata->addPropertyConstraint('repeatPassword', new NotBlank());
		$metadata->addPropertyConstraint('repeatPassword', new Length(array('min' => 8, 'max' => 40)));
	}
	
	public function checkPassword(ExecutionContextInterface $context)
	{
		if (!$this->user->checkPassword($this->encoderFactory, $this->oldPassword)) {
			$context->buildViolation('The specified password is invalid.')->atPath('oldPassword')->addViolation();
			return false;
		}
		
		if ($this->password != $this->repeatPassword) {
			$context->buildViolation('The specified passwords are not identical!')->atPath('password')->addViolation();
			return false;
		}
		
		if (!PasswordBuilder::isPasswordStrongEnough($this->password)) {
			$context->buildViolation('The password must contain lowercase, uppercase letters and numbers.')->atPath('password')->addViolation();
			return false;
		}
	}
	
	public function execute()
	{
		$passwordBuilder = new PasswordBuilder();
		$passwordBuilder->specifyPassword($this->password);
		$passwordBuilder->processChangedPassword($this->encoderFactory->getEncoder($this->user));
		
		$changeRequest = CredentialChangeRequest::forPassword($this->user, $passwordBuilder->getEncodedPassword(), $passwordBuilder->getSalt(), $_SERVER['REMOTE_ADDR'], time());
		$this->repository->insertCredentialChangeRequest($changeRequest);
		$this->dispatcher->dispatch(CantigaEvents::USER_CREDENTIAL_CHANGE, new CredentialChangeEvent($changeRequest));
	}
}
