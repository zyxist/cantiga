<?php
namespace Cantiga\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Cantiga\CoreBundle\Api\Workspace;

/**
 * Allows preparing the workspace to render - data population, etc.
 *
 * @author Tomasz Jędrzejewski
 */
class WorkspaceEvent extends Event
{
	/**
	 * @var Workspace
	 */
	private $workspace;
	/**
	 * Used for passing the information about the currently selected workgroup content to the menu.
	 * @var string
	 */
	private $currentWorkgroup = null;
	/**
	 * Used for generating the menu - extracted from breadcrumbs.
	 * @var string
	 */
	private $currentPage;
	
	public function __construct(Workspace $workspace)
	{
		$this->workspace = $workspace;
	}
	
	/**
	 * @return Workspace
	 */
	public function getWorkspace()
	{
		return $this->workspace;
	}
	
	public function getCurrentWorkgroup()
	{
		return $this->currentWorkgroup;
	}

	public function setCurrentWorkgroup($currentWorkgroup)
	{
		$this->currentWorkgroup = $currentWorkgroup;
		return $this;
	}
	
	public function getCurrentPage()
	{
		return $this->currentPage;
	}

	public function setCurrentPage($currentPage)
	{
		$this->currentPage = $currentPage;
		return $this;
	}
}
