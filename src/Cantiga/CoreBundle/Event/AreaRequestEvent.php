<?php
namespace Cantiga\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Cantiga\CoreBundle\Entity\AreaRequest;

/**
 * Represents an event, where something has happened to the area request.
 *
 * @author Tomasz Jędrzejewski
 */
class AreaRequestEvent extends Event
{
	private $areaRequest;
	
	public function __construct(AreaRequest $request)
	{
		$this->areaRequest = $request;
	}
	
	/**
	 * @return AreaRequest
	 */
	public function getAreaRequest()
	{
		return $this->areaRequest;
	}
}
