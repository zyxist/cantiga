<?php
namespace Cantiga\CoreBundle\Entity\Membership;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Capabilities\MembershipEntityInterface;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\MembershipLoaderInterface;
use Cantiga\Metamodel\MembershipRoleResolver;
use Cantiga\Metamodel\ProjectRepresentation;
use Doctrine\DBAL\Connection;

/**
 * Loads the information about the projects the user is member of.
 *
 * @author Tomasz Jędrzejewski
 */
class ProjectMembershipLoader implements MembershipLoaderInterface
{
	/**
	 * @var Connection
	 */
	private $conn;
	/**
	 * @var MembershipRoleResolver
	 */
	private $resolver;
	/**
	 * The user whose information is cached.
	 * @var int
	 */
	private $cachedUser = null;
	/**
	 * Cache, if the loading is called multiple times.
	 * @var array
	 */
	private $loadedItems;
	
	public function __construct(Connection $conn, MembershipRoleResolver $resolver)
	{
		$this->conn = $conn;
		$this->resolver = $resolver;
	}
	
	public function findMembership($slug, User $user)
	{
		$membership = Project::fetchMembership($this->conn, $this->resolver, $slug, $user->getId());
		if (false === $membership) {
			throw new ItemNotFoundException('The specified project is not available.', $slug);
		}
		$user->addRole('ROLE_PROJECT_AWARE');
		return $membership;
	}

	public function findProjectForEntity(MembershipEntityInterface $entity)
	{
		// I'm a project.
		return $entity;
	}

	public function loadProjectRepresentations(User $user)
	{
		if (empty($this->loadedItems)) {
			$this->loadedItems = $this->buildRepresentations($user);
			$this->cachedUser = $user->getId();
		} elseif ($this->cachedUser != $user->getId()) {
			return $this->buildRepresentations($user);
		}
		return $this->loadedItems;
	}
	
	private function buildRepresentations(User $user)
	{
		$projects = $this->conn->fetchAll('SELECT p.`id`, p.`name`, p.`slug`, c.`role`, c.`note` FROM `'.CoreTables::PROJECT_TBL.'` p '
			. 'INNER JOIN `'.CoreTables::PROJECT_MEMBER_TBL.'` c ON c.`projectId` = p.`id` '
			. 'WHERE p.`archived` = 0 AND c.`userId` = :userId ORDER BY p.`name`', [':userId' => $user->getId()]);
		$items = array();
		foreach ($projects as $proj) {
			$items[] = new ProjectRepresentation($proj['slug'], $proj['name'], 'project_dashboard', 'ProjectNominative: 0', 'primary', $this->resolver->getRole('Project', $proj['role']), $proj['note']);
		}
		return $items;
	}
}
