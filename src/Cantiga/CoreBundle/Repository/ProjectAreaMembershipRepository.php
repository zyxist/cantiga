<?php
namespace Cantiga\CoreBundle\Repository;

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\Exception\ItemNotFoundException;

/**
 * Manages the area membership information from the perspective of a project.
 *
 * @author Tomasz Jędrzejewski
 */
class ProjectAreaMembershipRepository extends AreaMembershipRepository
{
	/**
	 * Fetches an area by its ID and the project.
	 * 
	 * @param int $id
	 * @param Project $project
	 */
	public function getItem($id, Project $project)
	{
		$item = Area::fetchByProject($this->conn, $id, $project);
		if (false === $item) {
			throw new ItemNotFoundException('AreaNotFound', $id);
		}
		return $item;
	}
}
