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

use Doctrine\DBAL\Connection;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Exception\UserRegistrationException;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;

/**
 * Represents a registration attempt of a new user.
 */
class UserRegistration implements IdentifiableInterface, InsertableEntityInterface, RemovableEntityInterface
{
	private $id;
	private $login;
	private $name;
	private $password;
	private $salt;
	private $email;
	private $language;
	private $provisionKey;
	private $requestTime;
	private $requestIp;
	/**
	 * @var PasswordBuilder
	 */
	private $passwordBuilder;
	
	public static function newInstance($ip, $currentTime)
	{
		$item = new UserRegistration;
		$item->passwordBuilder = new PasswordBuilder();
		$item->requestIp = ip2long($ip);
		$item->requestTime = (int) $currentTime;
		return $item;
	}

	public static function fromArray($array, $prefix = '')
	{
		$item = new UserRegistration;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public static function getRelationships()
	{
		return ['language'];
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function setId($id)
	{
		DataMappers::noOverwritingId($this->id);
		$this->id = $id;
		return $this;
	}

	public function getLogin()
	{
		return $this->login;
	}
	
	public function getName()
	{
		return $this->name;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getLanguage()
	{
		return $this->language;
	}

	public function setLogin($login)
	{
		$this->login = $login;
		return $this;
	}
	
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}

	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	public function setLanguage($language)
	{
		$this->language = $language;
		return $this;
	}
	
	public function getProvisionKey()
	{
		return $this->provisionKey;
	}

	public function setProvisionKey($provisionKey)
	{
		$this->provisionKey = $provisionKey;
		return $this;
	}

	public function getSalt()
	{
		return $this->salt;
	}

	public function setSalt($salt)
	{
		$this->salt = $salt;
		return $this;
	}
	
	public function getRequestTime()
	{
		return $this->requestTime;
	}

	public function getRequestIp()
	{
		return $this->requestIp;
	}
	
	public function getFormattedRequestIp()
	{
		return long2ip($this->requestIp);
	}

	public function setRequestTime($requestTime)
	{
		$this->requestTime = $requestTime;
		return $this;
	}

	public function setRequestIp($requestIp)
	{
		$this->requestIp = $requestIp;
		return $this;
	}
	
	public function getPasswordBuilder()
	{
		return $this->passwordBuilder;
	}
	
	public function insert(Connection $conn)
	{
		$this->getPasswordBuilder()->exportPasswords($this);		
		$id = $conn->fetchColumn('SELECT `id` FROM `'.CoreTables::USER_REGISTRATION_TBL.'` WHERE `login` = :login', [':login' => $this->getLogin()]);
		if (!empty($id)) {
			throw new UserRegistrationException('The specified login cannot be used.');
		}
		$id = $conn->fetchColumn('SELECT `id` FROM `'.CoreTables::USER_TBL.'` WHERE `login` = :login', [':login' => $this->getLogin()]);
		if (!empty($id)) {
			throw new UserRegistrationException('The specified login cannot be used.');
		}
		
		$conn->insert(CoreTables::USER_REGISTRATION_TBL, DataMappers::pick($this, ['name', 'login', 'password', 'salt', 'email', 'language', 'provisionKey', 'requestIp', 'requestTime']));
		return $this->id = $conn->lastInsertId();
	}
	
	public function canRemove()
	{
		return true;
	}
	
	public function remove(Connection $conn)
	{
		$conn->delete(CoreTables::USER_REGISTRATION_TBL, DataMappers::pick($this, ['id']));
	}

	public function activate(string $provisionKey, string $timezone)
	{
		if ($this->provisionKey == $provisionKey) {
			$user = User::freshActive($this->getPassword(), $this->getSalt());
			$user->setLogin($this->getLogin());
			$user->setName($this->getName());
			$user->setEmail($this->getEmail());
			$user->setSettingsLanguage($this->getLanguage());
			$user->setSettingsTimezone($timezone);
			return $user;
		} else {
			throw new UserRegistrationException('Invalid provision key.');
		}
	}
}
