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
use Cantiga\CoreBundle\Entity\User;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\CoreBundle\Event\CredentialChangeEvent;
use Cantiga\UserBundle\Repository\ProfileRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class EmailChangeIntent
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
	
	public $password;
	public $email;
	
	public function __construct(ProfileRepository $repository, EventDispatcherInterface $dispatcher, EncoderFactoryInterface $encoderFactory, User $user)
	{
		$this->repository = $repository;
		$this->dispatcher = $dispatcher;
		$this->encoderFactory = $encoderFactory;
		$this->user = $user;
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata) {
		$metadata->addConstraint(new Callback('checkPassword'));
		
		$metadata->addPropertyConstraint('password', new NotBlank());
		$metadata->addPropertyConstraint('password', new Length(array('min' => 8, 'max' => 40)));
		$metadata->addPropertyConstraint('email', new Length(array('min' => 2, 'max' => 100)));
		$metadata->addPropertyConstraint('email', new Email());
	}
	
	public function checkPassword(ExecutionContextInterface $context)
	{
		if (!$this->user->checkPassword($this->encoderFactory, $this->password)) {
			$context->buildViolation('The specified password is invalid.')->atPath('password')->addViolation();
			return false;
		}
	}
	
	public function execute()
	{
		$changeRequest = CredentialChangeRequest::forEmail($this->user, $this->email, $_SERVER['REMOTE_ADDR'], time());
		$this->repository->insertCredentialChangeRequest($changeRequest);
		$this->dispatcher->dispatch(CantigaEvents::USER_CREDENTIAL_CHANGE, new CredentialChangeEvent($changeRequest));
	}
}
