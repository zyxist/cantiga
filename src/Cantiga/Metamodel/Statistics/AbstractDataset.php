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
namespace Cantiga\Metamodel\Statistics;

/**
 * Common code for processing the temporal datasets.
 *
 * @author Tomasz Jędrzejewski
 */
abstract class AbstractDataset
{
	protected function beforePacking()
	{
	}
	
	protected function afterPacking()
	{
	}
	
	abstract protected function packageIntoDataset(array $datePoint, array $values);
	abstract protected function fetchPackedValues(array $point);
	
	protected final function processPacked(array $data)
	{
		$this->beforePacking();
		
		$previousDatePoint = null;
		$currentDatePoint = null;
		$previousValues = null;
		$currentValues = null;
		foreach ($data as $point) {
			$currentDatePoint = $this->normalizeDatePoint($point['datePoint']);
			if (null !== $previousDatePoint) {
				while (!$this->isSame($previousDatePoint, $currentDatePoint)) {
					$previousDatePoint = $this->nextDatePoint($previousDatePoint);
					// don't pack the last iteration, as we'll be filling it with current values
					if (!$this->isSame($previousDatePoint, $currentDatePoint)) {
						$this->packageIntoDataset($previousDatePoint, $previousValues);
					}
				}
			}
			$previousValues = $currentValues = $this->fetchPackedValues($point);
			$this->packageIntoDataset($currentDatePoint, $currentValues);
			$previousDatePoint = $currentDatePoint;
		}
		$this->afterPacking();
	}

	protected final function normalizeDatePoint($datePoint)
	{
		$components = explode('-', $datePoint);
		$components[0] = (int) $components[0];
		$components[1] = (int) ltrim($components[1], '0');
		$components[2] = (int) ltrim($components[2], '0');
		return $components;
	}
	
	protected final function isSame(array $dp1, array $dp2)
	{
		return ($dp1[0] == $dp2[0] && $dp1[1] == $dp2[1] && $dp1[2] == $dp2[2]);
	}
	
	protected final function nextDatePoint(array $datePoint)
	{
		$days = cal_days_in_month(CAL_GREGORIAN, $datePoint[1], $datePoint[0]);
		if ($datePoint[2] == $days) {
			$datePoint[2] = 1;
			$datePoint[1]++;
			if ($datePoint[1] == 13) {
				$datePoint[0]++;
				$datePoint[1] = 1;
			}
		} else {
			$datePoint[2]++;
		}
		return $datePoint;
	}
	
	protected final function printableDatePoint(array $datePoint)
	{
		$str = $datePoint[0].'-';
		if ($datePoint[1] < 10) {
			$str .= '0'.$datePoint[1].'-';
		} else {
			$str .= $datePoint[1].'-';
		}
		if ($datePoint[2] < 10) {
			$str .= '0'.$datePoint[2];
		} else {
			$str .= $datePoint[2];
		}
		return $str;
	}
}
