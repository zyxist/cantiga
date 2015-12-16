<?php
namespace Cantiga\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Allows publishing links to the help pages in the top bar.
 *
 * @author Tomasz Jędrzejewski
 */
class ShowHelpEvent extends Event
{
	private $pages = array();
	
	public function setPages(array $pages)
	{
		$this->pages = $pages;
	}
	
	public function getPages()
	{
		return $this->pages;
	}
}
