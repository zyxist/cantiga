<?php
namespace Cantiga\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Emitted by the UI controller, the event is used to pass the breadcrumbs to
 * the view.
 *
 * @author Tomasz Jędrzejewski
 */
class ShowBreadcrumbsEvent extends Event
{
	private $breadcrumbs = array();
	
	public function getBreadcrumbs()
	{
		return $this->breadcrumbs;
	}
	
	public function setBreadcrumbs(array $bc)
	{
		$this->breadcrumbs = $bc;
	}
	
	public function hasBreadcrumbs()
	{
		return sizeof($this->breadcrumbs) > 0;
	}
}
