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

use Cantiga\Components\Hierarchy\Entity\Member;
use Cantiga\Components\Hierarchy\MembershipEntityInterface;
use Cantiga\Components\Hierarchy\MembershipRoleResolverInterface;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use Exception;

/**
 * Fetches the data for the project/group/area member lists, and allows viewing associated user profiles.
 */
class MemberlistRepository
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
		
	/**
	 * @param $membershipEntity Entity whose members we want to view
	 * @return array
	 */
	public function findMembers(MembershipEntityInterface $membershipEntity)
	{
		return $membershipEntity->findMembers($this->conn, $this->roleResolver);
	}
	
	/**
	 * @param $membershipEntity Entity whose members we want to view
	 * @param $id Member ID
	 * @return Member
	 */
	public function getItem(MembershipEntityInterface $membershipEntity, int $id): Member
	{
		$this->transaction->requestTransaction();
		try {
			$member = $membershipEntity->findMember($this->conn, $this->roleResolver, $id);
			if (false === $member) {
				throw new ItemNotFoundException('The specified member has not been found.');
			}
			return $member;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
}
