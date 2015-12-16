<?php
namespace Cantiga\CoreBundle\Event;

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\AreaRequest;

/**
 * Notification that the area request has been approved and the area has been
 * created.
 *
 * @author Tomasz Jędrzejewski
 */
class AreaRequestApprovedEvent extends AreaRequestEvent
{
	private $area;
	
	public function __construct(AreaRequest $request, Area $area)
	{
		parent::__construct($request);
		$this->area = $area;
	}
	
	/**
	 * @return Area
	 */
	public function getArea()
	{
		return $this->area;
	}
}
