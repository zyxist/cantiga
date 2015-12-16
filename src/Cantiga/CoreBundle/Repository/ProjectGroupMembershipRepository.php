<?php
namespace Cantiga\CoreBundle\Repository;

use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\Exception\ItemNotFoundException;

/**
 * Manages the group membership information.
 *
 * @author Tomasz Jędrzejewski
 */
class ProjectGroupMembershipRepository extends GroupMembershipRepository
{
	/**
	 * Fetches a group by its ID and the project.
	 * 
	 * @param int $id
	 * @param Project $project
	 */
	public function getItem($id, Project $project)
	{
		$item = Group::fetchByProject($this->conn, $id, $project);
		if (false === $item) {
			throw new ItemNotFoundException('GroupNotFound', $id);
		}
		return $item;
	}
}
