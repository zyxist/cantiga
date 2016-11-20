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
namespace Cantiga\CoreBundle\Api;

/**
 * A single element displayed in the breadcrumbs. This is a simple data storage
 * for the cases, where component A must return an entire breadcrumb item without
 * having the reference to the {@link Breadcrumbs} object. The item can be then
 * passed as a whole.
 *
 * @author Tomasz Jędrzejewski
 */
class BreadcrumbItem
{
	private $text;
	private $route;
	private $args;
	private $isEntry = false;
	
	public static function create($text, $route, array $args = [])
	{
		return new BreadcrumbItem($text, $route, $args);
	}
	
	public function __construct($text, $route, array $args = [])
	{
		$this->text = $text;
		$this->route = $route;
		$this->args = $args;
	}
	
	public function entry()
	{
		$this->isEntry = true;
		return $this;
	}
	
	public function getText()
	{
		return $this->text;
	}

	public function getRoute()
	{
		return $this->route;
	}

	public function getArgs()
	{
		return $this->args;
	}

	public function isEntry()
	{
		return $this->isEntry;
	}
}
