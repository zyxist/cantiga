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
namespace Cantiga\ExportBundle\Event;

use Cantiga\ExportBundle\Entity\ExportBlock;
use InvalidArgumentException;
use Symfony\Component\EventDispatcher\Event;

/**
 * Description of ExportEvent
 *
 * @author Tomasz Jędrzejewski
 */
class ExportEvent extends Event
{
	private $blocks = array();
	private $reporter = null;
	private $projectId;
	private $lastExportAt;
	
	public function __construct($projectId, $lastExportAt, $reporterCallback)
	{
		$this->projectId = $projectId;
		$this->lastExportAt = $lastExportAt;
		$this->reporter = $reporterCallback;
	}
	
	public function getProjectId()
	{
		return $this->projectId;
	}
	
	public function getLastExportAt()
	{
		return $this->lastExportAt;
	}
	
	public function addBlock($name, ExportBlock $block)
	{
		if (isset($this->blocks[$name])) {
			throw new InvalidArgumentException('Cannot overwrite export block: '.$name);
		}
		$this->blocks[$name] = $block;
		$callback = $this->reporter;
		$callback('Exporting block '.$name.': IDs '.$block->countIds().'; Updates '.$block->countUpdates());
	}
	
	/**
	 * Fetches the export block with the given name.
	 * 
	 * @param string $name Name of the block to return.
	 * @return ExportBlock
	 * @throws InvalidArgumentException
	 */
	public function getBlock($name)
	{
		if (!isset($this->blocks[$name])) {
			throw new InvalidArgumentException('No such export block: '.$name);
		}
		return $this->blocks[$name];
	}
	
	public function output()
	{
		$output = [];
		foreach ($this->blocks as $name => $block) {
			$output[$name] = $block->output();
		}
		return $output;
	}
}
