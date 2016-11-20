<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga contributors.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
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
