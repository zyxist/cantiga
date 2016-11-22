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
declare(strict_types=1);
namespace Cantiga\CoreBundle\Api;

/**
 * A single item that appears in the workgroup. Represented as a subnode in the menu,
 * where the root node is the workgroup. The work item points towards some controller
 * action.
 */
class WorkItem
{
	private $route;
	private $name;
	private $icon;
	
	public function __construct(string $route, string $name, string $icon = '')
	{
		$this->route = $route;
		$this->name = $name;
		$this->icon = $icon;
	}
	
	public function getRoute(): string
	{
		return $this->route;
	}

	public function getName(): string
	{
		return $this->name;
	}
	
	public function hasIcon(): bool
	{
		return !empty($this->icon);
	}
	
	public function getIcon(): string
	{
		return $this->icon;
	}
}
