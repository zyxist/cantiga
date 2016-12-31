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
namespace Cantiga\CoreBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\PasswordRecoveryRequest;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\CoreBundle\Exception\PasswordRecoveryException;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\QueryOperator;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Used for authentication purposes.
 *
 * @author Tomasz Jędrzejewski
 */
class AuthRepository implements UserProviderInterface
{
	const LASTVISIT_UPDATE_THRESHOLD_SEC = 60;
	
	/**
	 * @var Connection
	 */
	protected $conn;

	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}

	public function loadUserByUsername($username)
	{
		$user = $this->findUserByName($username);
		if (false !== $user) {
			return $user;
		}
		throw new UsernameNotFoundException(sprintf('Username "%s" not found.', $username));
	}

	public function refreshUser(UserInterface $original)
	{
		if (!$this->supportsClass(get_class($original))) {
			throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($original)));
		}
		$user = $this->findUserByName($original->getUsername());

		if (false === $user) {
			throw new UsernameNotFoundException(sprintf('Username "%s" not found.', $original->getUsername()));
		}
		
		if ($user->getLastVisit() < (time() - self::LASTVISIT_UPDATE_THRESHOLD_SEC)) {
			$this->conn->update(CoreTables::USER_TBL, ['lastVisit' => time()], ['id' => $user->getId()]);
		}

		return $user;
	}

	public function supportsClass($class)
	{
		return $class === 'Cantiga\\CoreBundle\\Entity\\User';
	}

	private function findUserByName($username)
	{
		return User::fetchByCriteria($this->conn, QueryClause::clause('u.`login` = :login', ':login', $username));
	}
	
	public function findUserByNameMail($username, $email)
	{
		return User::fetchByCriteria($this->conn, QueryOperator::op('AND')
			->expr(QueryClause::clause('u.`login` = :login', ':login', $username))
			->expr(QueryClause::clause('u.`email` = :email', ':email', $email))
		);
	}
	
	/**
	 * Creates and inserts into the database the password recovery request.
	 * 
	 * @param User $user The user that requests password recovery
	 * @param string $ip Current IP address of the request
	 * @return PasswordRecoveryRequest
	 */
	public function createPasswordRecoveryRequest(User $user, $ip)
	{
		$request = PasswordRecoveryRequest::create($user, $ip, time());
		
		$id = $this->conn->fetchColumn('SELECT `id` FROM `'.CoreTables::PASSWORD_RECOVERY_TBL.'` WHERE `requestIp` = :ip AND `requestTime` > :minTime', [
			':ip' => $request->getRequestIp(),
			':minTime' => time() - PasswordRecoveryRequest::REQUEST_INTERVAL_TIME
		]);
		if (!empty($id)) {
			throw new PasswordRecoveryException('PasswordRecoverySaveError');
		}
		
		$request->insert($this->conn);
		return $request;
	}
	
	/**
	 * Retrieves an existing password recovery request.
	 * 
	 * @param int $id
	 * @return PasswordRecoveryRequest
	 * @throws PasswordRecoveryException
	 */
	public function getPasswordRecoveryRequest($id)
	{
		$data = $this->conn->fetchAssoc('SELECT u.*, r.`id` AS `req_id`, r.`userId` AS `req_userId`, r.`status` AS `req_status`, r.`provisionKey` AS `req_provisionKey`, r.`requestIp` AS `req_requestIp`, r.`requestTime` AS `req_requestTime` '
			. 'FROM `'.CoreTables::PASSWORD_RECOVERY_TBL.'` r '
			. 'INNER JOIN `'.CoreTables::USER_TBL.'` u ON u.`id` = r.`userId` '
			. 'WHERE r.`id` = :requestId', [':requestId' => $id]);
		if (empty($data)) {
			throw new PasswordRecoveryException('Unknown registration request.');
		}
		return PasswordRecoveryRequest::fromArray(User::fromArray($data), $data);
	}
	
	public function updateRequest(PasswordRecoveryRequest $request)
	{
		$request->update($this->conn);
		$request->getUser()->updateCredentials($this->conn);
	}
}
