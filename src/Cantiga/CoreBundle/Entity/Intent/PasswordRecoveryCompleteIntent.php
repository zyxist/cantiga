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
namespace Cantiga\CoreBundle\Entity\Intent;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Cantiga\CoreBundle\Entity\PasswordBuilder;
use Cantiga\CoreBundle\Entity\PasswordRecoveryRequest;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\CoreBundle\Event\PasswordRecoveryEvent;
use Cantiga\CoreBundle\Repository\AuthRepository;

/**
 * @author Tomasz Jędrzejewski
 */
class PasswordRecoveryCompleteIntent
{
	private $repository;
	private $dispatcher;
	private $encoderFactory;

	public $password;
	public $repeatPassword;
	
	public function __construct(AuthRepository $repository, EventDispatcherInterface $dispatcher, EncoderFactoryInterface $encoderFactory)
	{
		$this->repository = $repository;
		$this->dispatcher = $dispatcher;
		$this->encoderFactory = $encoderFactory;
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata) {
		$metadata->addConstraint(new Callback('validate'));

		$metadata->addPropertyConstraint('password', new NotBlank());
		$metadata->addPropertyConstraint('password', new Length(array('min' => 8, 'max' => 40)));
		$metadata->addPropertyConstraint('repeatPassword', new NotBlank());
		$metadata->addPropertyConstraint('repeatPassword', new Length(array('min' => 8, 'max' => 40)));
	}
	
	public function validate(ExecutionContextInterface $context)
	{
		if ($this->password != $this->repeatPassword) {
			$context->buildViolation('The specified passwords are not identical!')->atPath('password')->addViolation();
			return false;
		}
		if (!PasswordBuilder::isPasswordStrongEnough($this->password)) {
			$context->buildViolation('The password must contain lowercase, uppercase letters and numbers.')->atPath('password')->addViolation();
			return false;
		}
	}
	
	public function execute(PasswordRecoveryRequest $request)
	{
		$request->complete();
		$passwordBuilder = new PasswordBuilder();
		$passwordBuilder->specifyPassword($this->password);
		$passwordBuilder->processChangedPassword($this->encoderFactory->getEncoder($request->getUser()));
		$passwordBuilder->exportPasswords($request->getUser());
		$this->repository->updateRequest($request);

		$this->dispatcher->dispatch(CantigaEvents::USER_PASSWORD_RECOVERY_COMPLETED, new PasswordRecoveryEvent($request));
	}
}
