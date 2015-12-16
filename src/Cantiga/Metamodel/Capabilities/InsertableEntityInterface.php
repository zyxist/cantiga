<?php
namespace Cantiga\Metamodel\Capabilities;

/**
 * To be implemented by every entity that can be inserted into the DB.
 * 
 * @author Tomasz Jędrzejewski
 */
interface InsertableEntityInterface
{
	/**
	 * This shall be a rather dumb method that does the simple INSERT. Repositories
	 * shall use this method, where necessary. This is why we pass just a single service
	 * to it.
	 * 
	 * @param \Doctrine\DBAL\Connection $conn
	 */
	public function insert(\Doctrine\DBAL\Connection $conn);
}
