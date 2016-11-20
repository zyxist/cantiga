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

/**
 * @author Tomasz Jędrzejewski
 */
class CredentialChangeRequest
{	
	const REQUEST_INTERVAL_TIME = 180;
	const MAX_VALIDITY_TIME = 1800;
	
	private $id;
	/**
	 * @var User
	 */
	private $user;
	private $requestIp;
	private $requestTime;
	private $provisionKey;
	
	private $password;
	private $salt;
	private $email;
	
	private $verified = false;
	
	public static function forEmail(User $user, $email, $ip, $time)
	{
		$item = new CredentialChangeRequest();
		$item->user = $user;
		$item->email = $email;
		$item->requestIp = ip2long($ip);
		$item->requestTime = $time;
		$item->provisionKey = sha1('dsSDfdjd'.rand(-2000000000, 2000000000).'ZSdDkjqi23Sdfdf'.$item->requestIp.$time.$user->getLogin());
		return $item;
	}
	
	public static function forPassword(User $user, $password, $salt, $ip, $time)
	{
		$item = new CredentialChangeRequest();
		$item->user = $user;
		$item->password = $password;
		$item->salt = $salt;
		$item->requestIp = ip2long($ip);
		$item->requestTime = $time;
		$item->provisionKey = sha1('SdfFSDfdjd'.rand(-2000000000, 2000000000).'ZSdDkjqi23df'.$item->requestIp.$time.$user->getLogin());
		return $item;
	}
	
	public static function fromArray(User $user, array $array)
	{
		$item = new CredentialChangeRequest();
		$item->user = $user;
		$item->id = $array['id'];
		$item->requestIp = $array['requestIp'];
		$item->requestTime = $array['requestTime'];
		$item->provisionKey = $array['provisionKey'];
		$item->password = $array['password'];
		$item->salt = $array['salt'];
		$item->email = $array['email'];
		return $item;
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function getRequestIp()
	{
		return $this->requestIp;
	}

	public function getRequestTime()
	{
		return $this->requestTime;
	}

	public function getProvisionKey()
	{
		return $this->provisionKey;
	}

	public function isVerified()
	{
		return $this->verified;
	}
	
	public function verify($provisionKey, $ip)
	{
		if (time() - self::MAX_VALIDITY_TIME > $this->requestTime) {
			return $this->verified = false;
		}
		return $this->verified = ($this->requestIp == ip2long($ip) && $this->provisionKey = $provisionKey);
	}
	
	public function export()
	{
		if ($this->verified) {
			if (!empty($this->email)) {
				$this->user->setEmail($this->email);
			}
			if (!empty($this->password)) {
				$this->user->setPassword($this->password);
				$this->user->setSalt($this->salt);
			}
		}
	}
	
	public function insert(Connection $conn)
	{
		$conn->insert(CoreTables::CREDENTIAL_CHANGE_TBL, [
			'userId' => $this->user->getId(),
			'requestIp' => $this->requestIp,
			'requestTime' => $this->requestTime,
			'provisionKey' => $this->provisionKey,
			'email' => $this->email,
			'password' => $this->password,
			'salt' => $this->salt,
		]);
		return $this->id = $conn->lastInsertId();
	}
	
	public function clear(Connection $conn)
	{
		$conn->delete(CoreTables::CREDENTIAL_CHANGE_TBL, ['id' => $this->id]);
	}
}
