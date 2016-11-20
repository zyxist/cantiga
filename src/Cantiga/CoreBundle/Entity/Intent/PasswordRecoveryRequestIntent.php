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
		if (false !== $user) {
			$request = $this->repository->createPasswordRecoveryRequest($user, $_SERVER['REMOTE_ADDR']);
			$this->dispatcher->dispatch(CantigaEvents::USER_PASSWORD_RECOVERY, new PasswordRecoveryEvent($request));
		}
	}
}
