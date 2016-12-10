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

use Cantiga\CoreBundle\Entity\PasswordBuilder;
use Cantiga\CoreBundle\Entity\UserRegistration;
use Cantiga\CoreBundle\Repository\UserRegistrationRepository;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class UserRegistrationIntent
{
	public $login;
	public $name;
	public $password;
	public $repeatPassword;
	public $email;
	public $language;
	public $acceptRules;
	
	private $repository;
	
	public function __construct(UserRegistrationRepository $repository)
	{
		$this->repository = $repository;
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata) {
		$metadata->addConstraint(new Callback('validate'));
		
		$metadata->addPropertyConstraint('login', new NotBlank());
		$metadata->addPropertyConstraint('login', new Length(array('min' => 6, 'max' => 40)));
		$metadata->addPropertyConstraint('name', new NotBlank());
		$metadata->addPropertyConstraint('name', new Length(array('min' => 5, 'max' => 60)));
		$metadata->addPropertyConstraint('password', new NotBlank());
		$metadata->addPropertyConstraint('password', new Length(array('min' => 8, 'max' => 40)));
		$metadata->addPropertyConstraint('repeatPassword', new NotBlank());
		$metadata->addPropertyConstraint('repeatPassword', new Length(array('min' => 8, 'max' => 40)));
		$metadata->addPropertyConstraint('email', new Length(array('min' => 2, 'max' => 100)));
		$metadata->addPropertyConstraint('email', new Email());
	}
	
	public function validate(ExecutionContextInterface $context)
	{
		if (!$this->acceptRules) {
			$context->buildViolation('You must accept the terms of service.')->addViolation();
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
		$item = UserRegistration::newInstance($_SERVER['REMOTE_ADDR'], time());
		$item->setLogin($this->login);
		$item->setName($this->name);
		$item->getPasswordBuilder()->specifyPassword($this->password);
		$item->setEmail($this->email);
		$item->setLanguage($this->language);
		
		$this->repository->register($item);	
	}
}
