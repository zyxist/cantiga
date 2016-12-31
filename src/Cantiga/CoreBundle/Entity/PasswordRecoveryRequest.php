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

class PasswordRecoveryRequest
{
	const STATUS_NEW = 0;
	const STATUS_OK = 1;
	const STATUS_FAILED = 2;
	const STATUS_PROCESSED = 3;
	
	const REQUEST_INTERVAL_TIME = 180;
	const MAX_VALIDITY_TIME = 1800;
	
	private $id;
	private $user;
	private $requestIp;
	private $requestTime;
	private $provisionKey;
	private $status = self::STATUS_NEW;
	
	public static function create(User $user, $ip, $time)
	{
		$item = new PasswordRecoveryRequest();
		$item->user = $user;
		$item->requestIp = ip2long($ip);
		$item->requestTime = $time;
		$item->provisionKey = sha1('dsSDfdjd'.rand(-2000000000, 2000000000).'ZSdDkjqi23df'.$item->requestIp.$time.$user->getLogin());
		return $item;
	}
	
	public static function fromArray(User $user, array $array)
	{
		$item = new PasswordRecoveryRequest();
		$item->user = $user;
		$item->id = $array['req_id'];
		$item->requestIp = $array['req_requestIp'];
		$item->requestTime = $array['req_requestTime'];
		$item->provisionKey = $array['req_provisionKey'];
		$item->status = $array['req_status'];
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

	public function getStatus()
	{
		return $this->status;
	}
	
	public function verify($provisionKey, $ip)
	{
		if (time() - self::MAX_VALIDITY_TIME > $this->requestTime) {
			$this->status = self::STATUS_FAILED;
		}
		
		if ($this->status == self::STATUS_NEW || $this->status == self::STATUS_OK) {
			if ($this->requestIp == ip2long($ip) && $this->provisionKey = $provisionKey) {
				$this->status = self::STATUS_OK;
			} else {
				$this->status = self::STATUS_FAILED;
			}
		}
	}
	
	public function complete()
	{
		if ($this->status == self::STATUS_OK) {
			$this->status = self::STATUS_PROCESSED;
		}
	}
	
	public function insert(Connection $conn)
	{
		$conn->insert(CoreTables::PASSWORD_RECOVERY_TBL, [
			'userId' => $this->user->getId(),
			'requestIp' => $this->requestIp,
			'requestTime' => $this->requestTime,
			'provisionKey' => $this->provisionKey,
			'status' => $this->status
		]);
		return $this->id = $conn->lastInsertId();
	}
	
	public function update(Connection $conn)
	{
		$conn->update(CoreTables::PASSWORD_RECOVERY_TBL, [
			'userId' => $this->user->getId(),
			'status' => $this->status
		], ['id' => $this->id]);
	}
}
