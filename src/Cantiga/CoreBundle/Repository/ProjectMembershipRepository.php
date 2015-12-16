<?php
namespace Cantiga\CoreBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Invitation;
use Cantiga\CoreBundle\Entity\Project;
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
 * Manages the project membership information, but from the project perspective
 * (no freelance adding of users, but invitations). In addition, we always work
 * in the context of the current project.
 *
 * @author Tomasz Jędrzejewski
 */
class ProjectMembershipRepository implements MembershipRepositoryInterface
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
	
	public function acceptInvitation(Invitation $invitation)
	{
		$role = $this->getRole($invitation->getRole());
		$note = $invitation->getNote();
		$project = Project::fetchActive($this->conn, $invitation->getResourceId());
		$project->joinMember($this->conn, $invitation->getUser(), $role, $note);
	}

	public function clearMembership(User $user)
	{
		$this->conn->executeUpdate('UPDATE `'.CoreTables::PROJECT_TBL.'` p INNER JOIN `'.CoreTables::PROJECT_MEMBER_TBL.'` m ON m.`projectId` = p.`id` '
			. 'SET p.`memberNum` = (p.`memberNum` - 1) WHERE m.`userId` = :userId', [':userId' => $user->getId()]);
		$this->conn->executeQuery('DELETE FROM `'.CoreTables::PROJECT_MEMBER_TBL.'` WHERE `userId` = :userId', [':userId' => $user->getId()]);
		return 'projectNum';
	}
}
