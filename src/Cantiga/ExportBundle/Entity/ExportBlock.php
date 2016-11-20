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
namespace Cantiga\ExportBundle\Entity;

/**
 * Represents a single pack of data that shall be exported. It consists of the
 * total list of all ID-s that shall be present in the external system, and the
 * full records of those entities that need to be updated.
 *
 * @author Tomasz Jędrzejewski
 */
class ExportBlock
{
	private $ids = [];
	private $updatedIds = [];
	private $update = [];
	
	public function addId($id)
	{
		$this->ids[] = $id;
	}
	
	public function addUpdatedId($updatedId)
	{
		$this->updatedIds[] = $updatedId;
	}
	
	public function addUpdate(array $update)
	{
		$this->update[] = $update;
	}
	
	public function countIds()
	{
		return sizeof($this->ids);
	}
	
	public function countUpdates()
	{
		return sizeof($this->update);
	}
	
	public function getIds()
	{
		return $this->ids;
	}
	
	public function getUpdatedIds()
	{
		return $this->updatedIds;
	}
	
	public function output()
	{
		return [
			'ids' => $this->ids,
			'update' => $this->update
		];
	}
}
