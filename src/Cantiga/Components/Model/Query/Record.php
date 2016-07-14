<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga Contributors.
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
namespace Cantiga\Components\Model\Query;

/**
 * This is an experimental API. For most of the time, our entities were just holders
 * for getters and setters. If we plan to move the remaining logic to queries and commands,
 * we can remove the need to write entities by providing a generic record.
 */
class Record
{
	private $type;
	private $id = null;
	private $properties = [];
	private $embedded = [];
	
	public function __construct(string $type, array $properties = [], array $embedded = [], int $id = null)
	{
		$this->type = $type;
		$this->id = $id;
		$this->properties = $properties;
		$this->embedded = $embedded;
	}
	
	
	public function getId()
	{
		if (null === $this->id) {
			throw new InvalidStateException('The record '.$this->type.' has no identity.');
		}
		return $this->id;
	}
	
	public function __get($name)
	{
		if (!isset($this->properties[$name])) {
			throw new InvalidArgumentException('No such property \''.$name.'\' in record '.$this->type);
		}
		return $this->properties[$name];
	}
	
	public function __isset($name)
	{
		return isset($this->properties[$name]);
	}
	
	public function add($property, $value)
	{
		if (isset($this->properties)) {
			throw new InvalidArgumentException('Cannot overwrite the property \''.$property.'\' in record '.$this->type.'\' with value \''.$value.'\'');
		}
		$this->properties[$property] = $value;
		return $this;
	}
	
	public function embedded($name)
	{
		if (!isset($this->embedded[$name])) {
			throw new InvalidArgumentException('No such embedded collection \''.$name.'\' in record '.$this->type);
		}
		return $this->embedded[$name];
	}
}
