<?php
namespace Cantiga\Metamodel\Capabilities;

/**
 * To be implemented by every entity that can be removed from the DB.
 * 
 * @author Tomasz Jędrzejewski
 */
interface RemovableEntityInterface
{
	/**
	 * Checks whether the entity can be removed.
	 * 
	 * @return boolean
	 */
	public function canRemove();
	/**
	 * This shall be a rather dumb method that does the simple DELETE. Repositories
	 * shall use this method, where necessary. This is why we pass just a single service
	 * to it.
	 * 
	 * @param \Doctrine\DBAL\Connection $conn
	 */
	public function remove(\Doctrine\DBAL\Connection $conn);
}
