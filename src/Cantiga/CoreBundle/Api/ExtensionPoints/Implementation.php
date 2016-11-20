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
namespace Cantiga\CoreBundle\Api\ExtensionPoints;

/**
 * Storage for the information about available implementations.
 *
 * @author Tomasz Jędrzejewski
 */
class Implementation
{
	/**
	 * Name of an extension point
	 * @var string
	 */
	private $extensionPoint;
	/**
	 * Name of a module
	 * @var string
	 */
	private $module;
	/**
	 * Name of a service
	 * @var string
	 */
	private $service;
	/**
	 * Human-readable name
	 * @var string
	 */
	private $name;
	
	public function __construct($extensionPoint, $module, $service, $name)
	{
		$this->extensionPoint = $extensionPoint;
		$this->module = $module;
		$this->service = $service;
		$this->name = $name;
	}
	
	public function getExtensionPoint()
	{
		return $this->extensionPoint;
	}

	public function getModule()
	{
		return $this->module;
	}

	public function getService()
	{
		return $this->service;
	}
	
	public function getName()
	{
		return $this->name;
	}
}
