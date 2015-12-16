<?php
namespace Cantiga\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Tomasz Jędrzejewski
 */
class ShowWorkspacesEvent extends Event
{
	private $workspaces = array();
	private $active = null;

	public function getActive()
	{
		return $this->active;
	}

	public function setActive($active)
	{
		$this->active = $active;
		return $this;
	}
	
	public function getWorkspaces()
	{
		return $this->workspaces;
	}
	
	public function setWorkspaces(array $workspaces)
	{
		$this->workspaces = $workspaces;
	}
}
