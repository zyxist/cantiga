<?php
namespace Cantiga\Metamodel\Capabilities;

/**
 * To be implemented by the repository.
 * 
 * @author Tomasz Jędrzejewski
 */
interface BatchRemoveInterface
{
	/**
	 * This method shall remove all the items with the specified ID-s.
	 * 
	 * @param array $items Array of ID-s to remove.
	 */
	public function batchRemove(array $items);
}
