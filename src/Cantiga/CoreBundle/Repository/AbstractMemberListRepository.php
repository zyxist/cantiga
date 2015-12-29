<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Capabilities\MembershipEntityInterface;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Join;
use Cantiga\Metamodel\Membership;
use Cantiga\Metamodel\MembershipRoleResolver;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\QueryOperator;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use Exception;
use PDO;

/**
 * Base class for constructing the repositories of the member lists.
 *
 * @author Tomasz Jędrzejewski
 */
abstract class AbstractMemberListRepository
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
	
	/**
	 * @return Name of the table with the membership information.
	 */
	protected abstract function membershipTable();
	/**
	 * @return Name of the column in the membership table which holds the ID of the membership entity.
	 */
	protected abstract function entityColumn();
	/**
	 * @return Name of the membership entity (without the namespace), used for role resolving.
	 */
	protected abstract function entityName();
	
	/**
	 * @param $membershipEntity Entity whose members we want to view
	 * @return array
	 */
	public function findMembers(MembershipEntityInterface $membershipEntity)
	{
		$stmt = $this->conn->prepare('SELECT i.`id`, i.`name`, i.`avatar`, i.`lastVisit`, p.`location`, m.`role` AS `membershipRole`, m.`note` AS `membershipNote` '
			. 'FROM `'.CoreTables::USER_TBL.'` i '
			. 'INNER JOIN `'.CoreTables::USER_PROFILE_TBL.'` p ON p.`userId` = i.`id` '
			. 'INNER JOIN `'.$this->membershipTable().'` m ON m.`userId` = i.`id` '
			. 'WHERE m.`'.$this->entityColumn().'` = :entityId AND i.`active` = 1 AND i.`removed` = 0 '
			. 'ORDER BY i.`name`');
		$stmt->bindValue(':entityId', $membershipEntity->getId());
		$stmt->execute();
		$results = [];
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$results[] = [
				'id' => $row['id'],
				'name' => $row['name'],
				'avatar' => $row['avatar'],
				'lastVisit' => $row['lastVisit'],
				'location' => $row['location'],
				'membership' => new Membership($membershipEntity, $this->roleResolver->getRole($this->entityName(), $row['membershipRole']), $row['membershipNote'])
			];
		}
		$stmt->closeCursor();
		return $results;
	}
	
	/**
	 * @param $membershipEntity Entity whose members we want to view
	 * @param $id User ID
	 * @return User
	 */
	public function getItem(MembershipEntityInterface $membershipEntity, $id)
	{
		$this->transaction->requestTransaction();
		try {
			$user = User::fetchLinkedProfile($this->conn, $this->roleResolver, $membershipEntity,
				Join::create($this->membershipTable(), 'm', QueryClause::clause('m.userId = u.id')),
				QueryOperator::op('AND')
					->expr(QueryClause::clause('m.'.$this->entityColumn().' = :entityId', ':entityId', $membershipEntity->getId()))
					->expr(QueryClause::clause('u.`id` = :userId', ':userId', $id)));
			
			if (false === $user) {
				throw new ItemNotFoundException('The specified user has not been found.');
			}
			return $user;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
}
