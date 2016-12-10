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

/**
 * Allows publishing links to the help pages in the top bar.
 */
class ShowHelpEvent extends Event
{
	private $pages = array();
	private $route = '';
	
	public function getRoute(): string
	{
		return $this->route;
	}

	public function setRoute($route)
	{
		$this->route = $route;
		return $this;
	}
	
	public function setPages(array $pages)
	{
		$this->pages = $pages;
	}
	
	public function getPages()
	{
		return $this->pages;
	}
}
