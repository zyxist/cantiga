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
namespace Cantiga\UserBundle\Repository;

use Doctrine\DBAL\Connection;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\CredentialChangeRequest;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\Transaction;

/**
 * Methods for managing the profile of the user that is logged in.
 */
class ProfileRepository
{
	/**
	 * @var Connection 
	 */
	private $conn;
	/**
	 * @var Transaction
	 */
	private $transaction;
	
	public function __construct(Connection $conn, Transaction $transaction)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
	}
	
	public function update(User $user)
	{
		$this->transaction->requestTransaction();
		$user->update($this->conn);
	}
	
	
	public function updateProfile(User $user)
	{
		$this->transaction->requestTransaction();
		$user->updateProfile($this->conn);
	}
	
	public function updateSettings(User $user)
	{
		$this->transaction->requestTransaction();
		$user->updateSettings($this->conn);
	}
	
	public function insertCredentialChangeRequest(CredentialChangeRequest $changeRequest)
	{
		$changeRequest->insert($this->conn);
	}
	
	public function getCredentialChangeRequest($id, User $currentUser)
	{
		$data = $this->conn->fetchAssoc('SELECT * FROM `'.CoreTables::CREDENTIAL_CHANGE_TBL.'` WHERE `id` = :id', [':id' => $id]);
		if (empty($data) || $data['userId'] != $currentUser->getId()) {
			throw new ModelException('The specified credential change request does not exist.');
		}
		return CredentialChangeRequest::fromArray($currentUser, $data);
	}
	
	public function completeCredentialChangeRequest(CredentialChangeRequest $changeRequest)
	{
		if ($changeRequest->isVerified()) {
			$changeRequest->getUser()->updateCredentials($this->conn);
		}
		$changeRequest->clear($this->conn);
	}
}
