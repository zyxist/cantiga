<?php
namespace Cantiga\Metamodel\Capabilities;

/**
 * To be implemented by every repository that supports editing entities of
 * certain type in the database.
 * 
 * @author Tomasz Jędrzejewski
 */
interface EditableRepositoryInterface
{
	public function update($entity);
}
