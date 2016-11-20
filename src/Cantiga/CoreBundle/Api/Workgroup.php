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
 * Groups several work items under a single label and icon. The main menu of the workspace
 * consists of one to several workgroups.
 *
 * @author Tomasz Jędrzejewski
 */
class Workgroup
{
	private $key;
	private $name;
	private $icon;
	private $order;
	
	public function __construct($key, $name, $icon, $order = 0)
	{
		$this->key = $key;
		$this->name = $name;
		$this->icon = $icon;
		$this->order = $order;
	}
	
	public function getKey()
	{
		return $this->key;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getIcon()
	{
		return $this->icon;
	}

	public function getOrder()
	{
		return $this->order;
	}
}
