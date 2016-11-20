<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Tomasz Jedrzejewski.
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
namespace Cantiga\Metamodel;

use Cantiga\Components\Hierarchy\Entity\MembershipRole;

/**
 * Information about the items (projects, groups, areas) the user is member of.
 * It is used for generating the menu.
 */
class ProjectRepresentation
{
	private $translationKey;
	private $route;
	private $slug;
	private $name;
	private $label;
	private $role;
	private $note;
	
	public function __construct($slug, $name, $route, $translationKey, $label, MembershipRole $role, $note)
	{
		$this->slug = $slug;
		$this->name = $name;
		$this->route = $route;
		$this->translationKey = $translationKey;
		$this->label = $label;
		$this->role = $role;
		$this->note = $note;
	}
	
	public function getTranslationKey()
	{
		return $this->translationKey;
	}

	public function getRoute()
	{
		return $this->route;
	}

	public function getSlug()
	{
		return $this->slug;
	}

	public function getName()
	{
		return $this->name;
	}
	
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @return MembershipRole
	 */
	public function getRole()
	{
		return $this->role;
	}

	public function getNote()
	{
		return $this->note;
	}

	public function setLabel($label)
	{
		$this->label = $label;
		return $this;
	}

	public function setRole($role)
	{
		$this->role = $role;
		return $this;
	}

	public function setNote($note)
	{
		$this->note = $note;
		return $this;
	}


}
