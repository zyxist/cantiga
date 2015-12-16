<?php
namespace Cantiga\CoreBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Invitation;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Capabilities\MembershipEntityInterface;
use Cantiga\Metamodel\Capabilities\MembershipRepositoryInterface;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\MembershipRole;
use Cantiga\Metamodel\MembershipRoleResolver;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use Exception;

/**
 * Manages the area membership information.
 *
 * @author Tomasz Jędrzejewski
 */
class AreaMembershipRepository implements MembershipRepositoryInterface
{
	/**
	 * @var Connection 
	 */
	protected $conn;
	/**
	 * @var Transaction
	 */
	protected $transaction;
	/**
	 * @var MembershipRoleResolver
	 */
	protected $roleResolver;
	
	public function __construct(Connection $conn, Transaction $transaction, MembershipRoleResolver $roleResolver)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->roleResolver = $roleResolver;
	}
	
	public function getMember(Area $item, $id)
	{
		if (!ctype_digit($id)) {
			throw new ModelException('Invalid user ID');
		}
		
		$this->transaction->requestTransaction();
		$user = $item->findMember($this->conn, $this->roleResolver, $id);
		if (empty($user)) {
			throw new ItemNotFoundException('The specified user has not been found.');
		}
		return $user;
	}
	
	public function getRole($id)
	{
		if (!ctype_digit($id)) {
			throw new ModelException('Invalid role ID');
		}
		
		return $this->roleResolver->getRole('Area', $id);
	}
	
	public function findMembers(Area $item)
	{
		$this->transaction->requestTransaction();
		return $item->findMembers($this->conn, $this->roleResolver);
	}

	public function joinMember(MembershipEntityInterface $item, User $user, MembershipRole $role, $note)
	{
		if($role->isUnknown()) {
			return ['status' => 0];
		}
		$this->transaction->requestTransaction();
		try {
			if ($item->joinMember($this->conn, $user, $role, $note)) {
				return ['status' => 1, 'data' => $item->findMembers($this->conn, $this->roleResolver)];
			}
			return ['status' => 0, 'data' => $item->findMembers($this->conn, $this->roleResolver)];
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function editMember(MembershipEntityInterface $item, User $user, MembershipRole $role, $note)
	{
		if($role->isUnknown()) {
			return ['status' => 0];
		}
		$this->transaction->requestTransaction();
		try {
			if ($item->editMember($this->conn, $user, $role, $note)) {
				return ['status' => 1, 'data' => $item->findMembers($this->conn, $this->roleResolver)];
			}
			return ['status' => 0, 'data' => $item->findMembers($this->conn, $this->roleResolver)];
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}

	public function removeMember(MembershipEntityInterface $item, User $user)
	{
		$this->transaction->requestTransaction();
		try {
			if ($item->removeMember($this->conn, $user)) {
				return ['status' => 1, 'data' => $item->findMembers($this->conn, $this->roleResolver)];
			}
			return ['status' => 0, 'data' => $item->findMembers($this->conn, $this->roleResolver)];
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function acceptInvitation(Invitation $invitation)
	{
		$role = $this->getRole($invitation->getRole());
		$note = $invitation->getNote();
		$item = Area::fetchActive($this->conn, $invitation->getResourceId());
		$item->joinMember($this->conn, $invitation->getUser(), $role, $note);
	}
	
	public function clearMembership(User $user)
	{
		$this->conn->executeUpdate('UPDATE `'.CoreTables::AREA_TBL.'` a INNER JOIN `'.CoreTables::AREA_MEMBER_TBL.'` m ON m.`areaId` = a.`id` '
			. 'SET a.`memberNum` = (a.`memberNum` - 1) WHERE m.`userId` = :userId', [':userId' => $user->getId()]);
		$this->conn->executeQuery('DELETE FROM `'.CoreTables::AREA_MEMBER_TBL.'` WHERE `userId` = :userId', [':userId' => $user->getId()]);
		return 'areaNum';
	}
}
