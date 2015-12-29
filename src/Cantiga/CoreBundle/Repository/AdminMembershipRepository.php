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

use Doctrine\DBAL\Connection;
use Exception;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Capabilities\MembershipEntityInterface;
use Cantiga\Metamodel\Capabilities\MembershipRepositoryInterface;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\MembershipRole;
use Cantiga\Metamodel\MembershipRoleResolver;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\Transaction;

/**
 * Manages the project membership information.
 *
 * @author Tomasz Jędrzejewski
 */
class AdminMembershipRepository implements MembershipRepositoryInterface
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
	 * @var MembershipRoleResolver
	 */
	private $roleResolver;
	
	public function __construct(Connection $conn, Transaction $transaction, MembershipRoleResolver $roleResolver)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->roleResolver = $roleResolver;
	}
	
	public function findActiveProjects()
	{
		return $this->conn->fetchAll('SELECT id, name FROM `'.CoreTables::PROJECT_TBL.'` WHERE `archived` = 0 ORDER BY name');
	}
	
	public function getProject($projectId)
	{
		if (!ctype_digit($projectId)) {
			throw new ModelException('Invalid project ID');
		}
		
		$this->transaction->requestTransaction();
		$project = Project::fetchActive($this->conn, $projectId);
		if (empty($project)) {
			throw new ItemNotFoundException('The specified project has not been found.');
		}
		return $project;
	}
	
	public function getUserByEmail($email)
	{
		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			throw new ModelException('Invalid e-mail address');
		}
		$this->transaction->requestTransaction();
		$user = User::fetchByCriteria($this->conn, QueryClause::clause('u.`email` = :email', ':email', $email));
		if (empty($user)) {
			throw new ItemNotFoundException('The specified user has not been found.');
		}
		return $user;
	}
	
	public function getMember(Project $project, $id)
	{
		if (!ctype_digit($id)) {
			throw new ModelException('Invalid user ID');
		}
		
		$this->transaction->requestTransaction();
		$user = $project->findMember($this->conn, $this->roleResolver, $id);
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
		
		return $this->roleResolver->getRole('Project', $id);
	}

	public function findHints(Project $project, $query)
	{
		$this->transaction->requestTransaction();
		return $project->findHints($this->conn, $query);
	}
	
	public function findMembers(Project $project)
	{
		$this->transaction->requestTransaction();
		return $project->findMembers($this->conn, $this->roleResolver);
	}

	public function joinMember(MembershipEntityInterface $project, User $user, MembershipRole $role, $note)
	{
		if($role->isUnknown()) {
			return ['status' => 0];
		}
		$this->transaction->requestTransaction();
		try {
			if ($project->joinMember($this->conn, $user, $role, $note)) {
				return ['status' => 1, 'data' => $project->findMembers($this->conn, $this->roleResolver)];
			}
			return ['status' => 0, 'data' => $project->findMembers($this->conn, $this->roleResolver)];
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function editMember(MembershipEntityInterface $project, User $user, MembershipRole $role, $note)
	{
		if($role->isUnknown()) {
			return ['status' => 0];
		}
		$this->transaction->requestTransaction();
		try {
			if ($project->editMember($this->conn, $user, $role, $note)) {
				return ['status' => 1, 'data' => $project->findMembers($this->conn, $this->roleResolver)];
			}
			return ['status' => 0, 'data' => $project->findMembers($this->conn, $this->roleResolver)];
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}

	public function removeMember(MembershipEntityInterface $project, User $user)
	{
		$this->transaction->requestTransaction();
		try {
			if ($project->removeMember($this->conn, $user)) {
				return ['status' => 1, 'data' => $project->findMembers($this->conn, $this->roleResolver)];
			}
			return ['status' => 0, 'data' => $project->findMembers($this->conn, $this->roleResolver)];
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}

	public function acceptInvitation(\Cantiga\CoreBundle\Entity\Invitation $invitation)
	{
	}

	public function clearMembership(User $user)
	{
		return null;
	}

}
