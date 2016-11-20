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
namespace Cantiga\CoreBundle\Entity;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Virtual entity that represents a password.
 *
 * @author Tomasz Jędrzejewski
 */
class PasswordBuilder
{
	private $password;	
	private $outputEncodedPassword;
	private $outputSalt;
	
	private $provisionKey;
	
	public function specifyPassword($password)
	{
		$this->password = $password;
	}
	
	public function getProvisionKey()
	{
		return $this->provisionKey;
	}
	
	public function processInitialPassword(PasswordEncoderInterface $encoder)
	{		
		$this->generateProvisionKey();
		$this->generateSalt();
		$this->outputEncodedPassword = $encoder->encodePassword($this->password, $this->outputSalt);
	}
	
	public function processChangedPassword(PasswordEncoderInterface $encoder)
	{
		$this->generateSalt();
		$this->outputEncodedPassword = $encoder->encodePassword($this->password, $this->outputSalt);
	}
	
	public function exportPasswords($entity)
	{
		$entity->setPassword($this->outputEncodedPassword);
		$entity->setSalt($this->outputSalt);
		if (method_exists($entity, 'setProvisionKey')) {
			$entity->setProvisionKey($this->provisionKey);
		}
	}
	
	public function getEncodedPassword()
	{
		return $this->outputEncodedPassword;
	}
	
	public function getSalt()
	{
		return $this->outputSalt;
	}
	
	protected function generateSalt()
	{
		if (!isset($_SERVER['REMOTE_ADDR'])) {
			// for the purpose of autotests
			$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		}
		$this->outputSalt = sha1(time() . $_SERVER['REMOTE_ADDR'] . next($_SERVER) . rand(-200000000, 2000000000));
	}
	
	protected function generateProvisionKey()
	{
		$this->provisionKey = sha1($_SERVER['REMOTE_ADDR'] . 'Fks89Xjd' . time(). 'dfssdfsd DSFsdeD' . rand(-200000000, 2000000000));
	}
	
	/**
	 * We specify the new passwords in several places. This code makes the validation rules consistent.
	 * 
	 * @param ClassMetadata $metadata
	 */
	public static function setPasswordValidationRules(ClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('password', new NotBlank());
		$metadata->addPropertyConstraint('password', new Length(array('min' => 8, 'max' => 40)));
		$metadata->addPropertyConstraint('repeatPassword', new NotBlank());
		$metadata->addPropertyConstraint('repeatPassword', new Length(array('min' => 8, 'max' => 40)));
	}
	
	/**
	 * @param type $password
	 */
	public static function isPasswordStrongEnough($password)
	{
		$length = strlen($password);
		$smallLetter = false;
		$bigLetter = false;
		$number = false;
		for ($i = 0; $i < $length; $i++) {
			if (ctype_lower($password[$i])) {
				$smallLetter = true;
			} elseif (ctype_upper($password[$i])) {
				$bigLetter = true;
			} elseif (ctype_digit($password[$i])) {
				$number = true;
			}
		}
		return $smallLetter && $bigLetter && $number;
	}
}
