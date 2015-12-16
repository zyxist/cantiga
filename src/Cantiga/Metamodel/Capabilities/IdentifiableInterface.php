<?php
namespace Cantiga\Metamodel\Capabilities;

/**
 * To be implemented by all entities that have a single, numeric ID.
 * 
 * @author Tomasz Jędrzejewski
 */
interface IdentifiableInterface
{
	public function getId();
}
