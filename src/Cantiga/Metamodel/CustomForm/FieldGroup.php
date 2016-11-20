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
namespace Cantiga\Metamodel\CustomForm;

/**
 * Groups a couple of form fields into a fieldset.
 *
 * @author Tomasz Jędrzejewski
 */
class FieldGroup implements \IteratorAggregate
{
	private $name;
	private $description;
	private $fields = array();
	
	public function __construct($name, $description)
	{
		$this->name = $name;
		$this->description = $description;
	}
	
	public function addField($name)
	{
		$this->fields[] = $name;
	}
	
	public function getName()
	{
		return $this->name;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getFields()
	{
		return $this->fields;
	}
	
	public function getIterator()
	{
		return new \ArrayIterator($this->fields);
	}
}
