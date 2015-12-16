<?php
namespace Cantiga\Metamodel\Capabilities;

/**
 * To be implemented by every entity that can be updated in the DB.
 * 
 * @author Tomasz Jędrzejewski
 */
interface EditableEntityInterface
{
	/**
	 * This shall be a rather dumb method that does the simple UPDATE. Repositories
	 * shall use this method, where necessary. This is why we pass just a single service
	 * to it.
	 * 
	 * @param \Doctrine\DBAL\Connection $conn
	 */
	public function update(\Doctrine\DBAL\Connection $conn);
}
