<?php
namespace Cantiga\CoreBundle\Entity\Context;

use Doctrine\DBAL\Connection;
use LogicException;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Membership;
use Cantiga\Metamodel\MembershipRoleResolver;

/**
 * Description of UserContext
 *
 * @author Tomasz Jędrzejewski
 */
class UserMembershipContext
{
	/**
	 * @var User
	 */
	private $user;
	/**
	 * @var Project
	 */
	private $activeProject = null;
	private $activeArea = null;

	/**
	 * @var array
	 */
	private $memberProjects;
	/**
	 *
	 * @var array
	 */
	private $memberGroups;
	/**
	 * @var array
	 */
	private $memberAreas;
	/**
	 * @var Connection
	 */
	private $conn;
	/**
	 * @var MembershipRoleResolver
	 */
	private $roleResolver;
	
	public static function forCurrentlyLoggedUser(User $user, Connection $conn, MembershipRoleResolver $roleResolver)
	{
		$ctx = new UserMembershipContext();
		$ctx->user = $user;
		$ctx->conn = $conn;
		$ctx->roleResolver = $roleResolver;
		$ctx->memberProjects = self::findMemberProjects($user, $conn);
		$ctx->memberAreas = self::findMemberAreas($user, $conn);
		return $ctx;
	}
	
	public static function forUser(User $user, Connection $conn, MembershipRoleResolver $roleResolver)
	{
		$ctx = new UserMembershipContext();
		$ctx->user = $user;
		$ctx->roleResolver = $roleResolver;
		$ctx->memberProjects = self::findMemberProjects($user, $conn);
		$ctx->memberAreas = self::findMemberAreas($user, $conn);
		
		return $ctx;
	}
	
	private static function findMemberProjects(User $user, Connection $conn)
	{
		$projects = $conn->fetchAll('SELECT p.`id`, p.`name`, c.`role`, c.`note` FROM `'.CoreTables::PROJECT_TBL.'` p '
			. 'INNER JOIN `'.CoreTables::PROJECT_MEMBER_TBL.'` c ON c.`projectId` = p.`id` '
			. 'WHERE p.`archived` = 0 AND c.`userId` = :userId ORDER BY p.`name`', [':userId' => $user->getId()]);
		if (sizeof($projects) > 0) {
			$user->addRole('ROLE_PROJECT_AWARE');
		}
		return $projects;
	}
	
	private static function findMemberAreas(User $user, Connection $conn)
	{
		return [];
	}

	public function detectActiveProject()
	{
		if (sizeof($this->memberProjects) > 0) {
			if ($this->projectNotSet()) {
				// If no active project select, always select the first project by default.
				$id = $this->memberProjects[0]['id'];
				$this->user->setSettingsProject($id);
				$this->conn->update(CoreTables::USER_PROFILE_TBL, ['settingsProject' => $id], ['userId' => $this->user->getId()]);
			} else {
				$id = $this->user->getSettingsProject();
			}
			$this->activeProject = Project::fetchActive($this->conn, $id);
			$membership = $this->createProjectMembership();
			if (false !== $membership) {
				User::installMembershipInformation($this->user, $membership);
				$this->user->addRole($membership->getRole()->getAuthRole());
			}
		} else {
			User::installMembershipInformation($this->user, new Membership());
			if (null !== $this->user->getSettingsProject()) {
				$this->conn->update(CoreTables::USER_PROFILE_TBL, ['settingsProject' => null], ['userId' => $this->user->getId()]);
			}
			$this->activeProject = false;
		}
	}
	
	public function hasActiveProject()
	{
		if (null === $this->activeProject) {
			$this->activeProject = $this->fetchActiveProject();
		}
		return ($this->activeProject !== false);
	}
	
	/**
	 * Use this to get ID of one of the projects associated with the currently logged user, without
	 * fetching the project metadata, and without changing anything in the user profile.
	 * 
	 * @return int
	 */
	public function getRecommendedProjectId()
	{
		if (sizeof($this->memberProjects) > 0) {
			if ($this->projectNotSet()) {
				// If no active project select, always select the first project by default.
				return $this->memberProjects[0]['id'];
			} else {
				return $this->user->getSettingsProject();
			}
		}
		return null;
	}

	/**
	 * @return Project
	 * @throws LogicException
	 */
	public function getActiveProject()
	{
		if (null === $this->activeProject) {
			if (null === $this->user->getSettingsProject()) {
				$this->activeProject = false;
			} else {
				$this->activeProject = Project::fetchActive($this->conn, $this->user->getSettingsProject());
			}
		}
		if (false === $this->activeProject) {
			throw new LogicException('This user does not have any active project.');
		}
		return $this->activeProject;
	}
	
	public function getMemberProjects()
	{
		return $this->memberProjects;
	}
	
	/**
	 * Switches the user to another active project he is member of.
	 * @param int $projectId
	 * @throws \ModelException
	 */
	public function switchProject($projectId)
	{
		$project = Project::fetchByMembership($this->conn, $projectId, $this->user->getId());
		if (!empty($project) && !$project->getArchived()) {
			$this->conn->update(CoreTables::USER_PROFILE_TBL, ['settingsProject' => $projectId], ['userId' => $this->user->getId()]);
		} else {
			throw new \ModelException('You are not a member of this project.');
		}
	}
	
	private function projectNotSet()
	{
		$val = $this->user->getSettingsProject();
		return (null === $val || 0 == $val);
	}
	
	private function createProjectMembership()
	{
		foreach ($this->memberProjects as $project) {
			if ($project['id'] == $this->activeProject->getId()) {
				return new Membership($this->activeProject, $this->roleResolver->getRole('Project', $project['role']), $project['note']);
			}
		}
		return false;
	}
}
