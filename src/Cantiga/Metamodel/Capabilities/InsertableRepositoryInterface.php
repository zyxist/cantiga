<?php
namespace Cantiga\Metamodel\Capabilities;

/**
 * To be implemented by every repository that supports persisting entities of
 * certain type in the database.
 * 
 * @author Tomasz Jędrzejewski
 */
interface InsertableRepositoryInterface
{
	public function insert($entity);
}
