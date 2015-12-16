<?php
namespace Cantiga\Metamodel\Capabilities;

/**
 * To be implemented by every repository that supports removing entities of
 * certain type from the database.
 * 
 * @author Tomasz Jędrzejewski
 */
interface RemovableRepositoryInterface
{
	public function remove($entity);
}
