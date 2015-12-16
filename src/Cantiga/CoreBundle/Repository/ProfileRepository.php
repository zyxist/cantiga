<?php
namespace Cantiga\CoreBundle\Repository;

use Doctrine\DBAL\Connection;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\CredentialChangeRequest;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\Transaction;

/**
 * Methods for managing the profile of the user that is logged in.
 *
 * @author Tomasz Jędrzejewski
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
