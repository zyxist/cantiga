<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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
namespace Cantiga\ForumBundle\Entity;

/**
 * Generic parent, if we need only the basic data.
 */
class ForumParent implements ForumParentInterface
{
	private $id;
	private $name;
	private $linkable;
	private $parent;
	
	public function __construct($id, $name, $linkable, $parent)
	{
		$this->id = $id;
		$this->name = $name;
		$this->linkable = $linkable;
		$this->parent = $parent;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function isLinkable()
	{
		return $this->linkable;
	}

	public function getParent()
	{
		return $this->parent;
	}
}
