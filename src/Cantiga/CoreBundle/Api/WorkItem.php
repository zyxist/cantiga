<?php
namespace Cantiga\CoreBundle\Api;

/**
 * A single item that appears in the workgroup. Represented as a subnode in the menu,
 * where the root node is the workgroup. The work item points towards some controller
 * action.
 *
 * @author Tomasz Jędrzejewski
 */
class WorkItem
{
	private $route;
	private $name;
	
	public function __construct($route, $name)
	{
		$this->route = $route;
		$this->name = $name;
	}
	
	public function getRoute()
	{
		return $this->route;
	}

	public function getName()
	{
		return $this->name;
	}
}
