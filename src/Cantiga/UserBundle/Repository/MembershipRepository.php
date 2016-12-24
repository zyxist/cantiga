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
declare(strict_types=1);
namespace Cantiga\UserBundle\Repository;

use Cantiga\Components\Hierarchy\Entity\Member;
use Cantiga\Components\Hierarchy\Entity\MembershipRole;
use Cantiga\Components\Hierarchy\MembershipEntityInterface;
use Cantiga\Components\Hierarchy\MembershipRepositoryInterface;
use Cantiga\Components\Hierarchy\MembershipRoleResolverInterface;
use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Invitation;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use Exception;

/**
 * Management of the place members, including invitations.
 */
class MembershipRepository implements MembershipRepositoryInterface
{
	/**
	 * @var Connection 
	 */
	private $conn;
	/**
	 * @var Transaction
	 */
	private $transaction;
	/**
	 * @var MembershipRoleResolverInterface
	 */
	private $roleResolver;
	
	public function __construct(Connection $conn, Transaction $transaction, MembershipRoleResolverInterface $roleResolver)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->roleResolver = $roleResolver;
	}
	
	public function getUserPlaces(CantigaUserRefInterface $user): array
	{
		if ($user instanceof User) {
			return $user->findPlaces($this->conn, $this->roleResolver);
		}
		return [];
	}
	
	public function getMember(MembershipEntityInterface $place, int $id): CantigaUserRefInterface
	{		
		$this->transaction->requestTransaction();
		$user = $place->findMember($this->conn, $this->roleResolver, $id);
		if (empty($user)) {
			throw new ItemNotFoundException('The specified user has not been found.', $id);
		}
		return $user;
	}
	
	public function getRole(MembershipEntityInterface $place, int $id): MembershipRole
	{		
		return $this->roleResolver->getRole($place->getType(), $id);
	}
	
	public function findMembers(MembershipEntityInterface $place)
	{
		$this->transaction->requestTransaction();
		return Member::collectionAsArray($place->findMembers($this->conn, $this->roleResolver));
	}

	public function joinMember(MembershipEntityInterface $place, CantigaUserRefInterface $user, MembershipRole $role, $note, $showDownstreamContactData)
	{
		if($role->isUnknown()) {
			return ['status' => 0];
		}
		$this->transaction->requestTransaction();
		try {
			if ($place->joinMember($this->conn, $user, $role, $note, $showDownstreamContactData)) {
				return ['status' => 1, 'data' => Member::collectionAsArray($place->findMembers($this->conn, $this->roleResolver))];
			}
			return ['status' => 0, 'data' => Member::collectionAsArray($place->findMembers($this->conn, $this->roleResolver))];
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function editMember(MembershipEntityInterface $place, CantigaUserRefInterface $user, MembershipRole $role, $note, $showDownstreamContactData)
	{
		if($role->isUnknown()) {
			return ['status' => 0];
		}
		$this->transaction->requestTransaction();
		try {
			if ($place->editMember($this->conn, $user, $role, $note, $showDownstreamContactData)) {
				return ['status' => 1, 'data' => Member::collectionAsArray($place->findMembers($this->conn, $this->roleResolver))];
			}
			return ['status' => 0, 'data' => Member::collectionAsArray($place->findMembers($this->conn, $this->roleResolver))];
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}

	public function removeMember(MembershipEntityInterface $place, CantigaUserRefInterface $user)
	{
		$this->transaction->requestTransaction();
		try {
			if ($place->removeMember($this->conn, $user)) {
				return ['status' => 1, 'data' => Member::collectionAsArray($place->findMembers($this->conn, $this->roleResolver))];
			}
			return ['status' => 0, 'data' => Member::collectionAsArray($place->findMembers($this->conn, $this->roleResolver))];
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}

	public function acceptInvitation(Invitation $invitation)
	{
		$role = $this->getRole($invitation->getPlace(), $invitation->getRole());
		$note = $invitation->getNote();
		
		$invitation->getPlace()->joinMember($this->conn, $invitation->getUser(), $role, $note, $invitation->getShowDownstreamContactData());
	}

	public function clearMembership(User $user)
	{
		$this->conn->executeUpdate('UPDATE `'.CoreTables::PROJECT_TBL.'` p INNER JOIN `'.CoreTables::PROJECT_MEMBER_TBL.'` m ON m.`projectId` = p.`id` '
			. 'SET p.`memberNum` = (p.`memberNum` - 1) WHERE m.`userId` = :userId', [':userId' => $user->getId()]);
		$this->conn->executeQuery('DELETE FROM `'.CoreTables::PROJECT_MEMBER_TBL.'` WHERE `userId` = :userId', [':userId' => $user->getId()]);
		return 'projectNum';
	}
}
