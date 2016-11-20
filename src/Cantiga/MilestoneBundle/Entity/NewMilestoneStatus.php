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
namespace Cantiga\MilestoneBundle\Entity;

use LogicException;

/**
 * New status of the milestone to be returned by the activation event callback.
 *
 * @author Tomasz Jędrzejewski
 */
class NewMilestoneStatus
{
	private $completed;
	private $progress;
	private $change = false;
	
	public static function create($value)
	{
		$item = new NewMilestoneStatus();
		$item->change = true;
		if (is_bool($value)) {
			if ($value) {
				$item->completed = true;
				$item->progress = 100;
			} else {
				$item->completed = false;
				$item->progress = 0;
			}
		} elseif (is_int($value)) {
			if ($value == 100) {
				$item->completed = true;
				$item->progress = 100;
			} elseif ($value < 100) {
				$item->completed = false;
				$item->progress = $value;
			}
			if ($value > 100 || $value < 0) {
				throw new LogicException('Invalid type of the milestone status value: must be either boolean or integer from range [0-100], '.$value.' given.');
			}
		} else {
			throw new LogicException('Invalid type of the milestone status value: must be either boolean or integer from range [0-100].');
		}
		return $item;
	}
	
	public static function noChange()
	{
		return new NewMilestoneStatus(); 
	}
	
	public function isChanged()
	{
		return $this->change;
	}
	
	public function isCompleted()
	{
		return $this->completed;
	}

	public function getProgress()
	{
		return $this->progress;
	}
}
