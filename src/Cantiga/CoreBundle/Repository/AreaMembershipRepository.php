<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Tomasz Jedrzejewski.
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

use Cantiga\Components\Hierarchy\Entity\Member;
use Cantiga\Components\Hierarchy\Entity\MembershipRole;
use Cantiga\Components\Hierarchy\MembershipEntityInterface;
use Cantiga\Components\Hierarchy\MembershipRepositoryInterface;
use Cantiga\Components\Hierarchy\MembershipRoleResolverInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Invitation;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use Exception;

/**
 * Manages the area membership information.
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
	 * @var MembershipRoleResolverInterface
	 */
	protected $roleResolver;
	
	public function __construct(Connection $conn, Transaction $transaction, MembershipRoleResolverInterface $roleResolver)
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
		return Member::collectionAsArray($item->findMembers($this->conn, $this->roleResolver));
	}

	public function joinMember(MembershipEntityInterface $item, User $user, MembershipRole $role, $note)
	{
		if($role->isUnknown()) {
			return ['status' => 0];
		}
		$this->transaction->requestTransaction();
		try {
			if ($item->joinMember($this->conn, $user, $role, $note)) {
				return ['status' => 1, 'data' => Member::collectionAsArray($item->findMembers($this->conn, $this->roleResolver))];
			}
			return ['status' => 0, 'data' => Member::collectionAsArray($item->findMembers($this->conn, $this->roleResolver))];
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
				return ['status' => 1, 'data' => Member::collectionAsArray($item->findMembers($this->conn, $this->roleResolver))];
			}
			return ['status' => 0, 'data' => Member::collectionAsArray($item->findMembers($this->conn, $this->roleResolver))];
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
				return ['status' => 1, 'data' => Member::collectionAsArray($item->findMembers($this->conn, $this->roleResolver))];
			}
			return ['status' => 0, 'data' => Member::collectionAsArray($item->findMembers($this->conn, $this->roleResolver))];
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
