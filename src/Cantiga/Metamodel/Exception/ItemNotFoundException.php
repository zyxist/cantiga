<?php
namespace Cantiga\Metamodel\Exception;

/**
 * Notifies that an entity has not been found.
 *
 * @author Tomasz Jędrzejewski
 */
class ItemNotFoundException extends ModelException
{
	private $itemId;
	
	public function __construct($message, $itemId = null)
	{
		parent::__construct($message);
		$this->itemId = $itemId;
	}
	
	public function getItemId()
	{
		return $this->itemId;
	}
}