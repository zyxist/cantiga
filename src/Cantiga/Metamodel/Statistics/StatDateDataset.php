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
namespace Cantiga\Metamodel\Statistics;

/**
 * Prepares the statistical data to be displayed on a bar chart, where the X axis is the
 * timeline. The implementation assumes sparse storage of individual dates in the database,
 * and fills in the holes with the last known point of measure.
 *
 * @author Tomasz Jędrzejewski
 */
class StatDateDataset
{
	const TYPE_PACKED = 0;
	const TYPE_LOOSE = 1;
	
	private $type;
	private $datasetDefs = [];
	
	private $labels;
	private $datasets;
	
	public function __construct($type)
	{
		$this->type = $type;
	}
	
	public function dataset($name)
	{
		$this->datasetDefs[] = $name;
		return $this;
	}
	
	public function process(array $data)
	{
		if ($this->type == self::TYPE_LOOSE) {
			$this->processLoose($data);
		} else {
			$this->processPacked($data);
		}
		return $this;
	}
	
	public function getLabels()
	{
		return $this->labels;
	}
	
	public function getDataset($id)
	{
		if (!isset($this->datasets[$id])) {
			throw new ChartException('Unknown dataset: '.$id);
		}
		return $this->datasets[$id];
	}
	
	private function processPacked(array $data) {
		$this->labels = [];
		$this->datasets = $this->prepareDatasets();
		
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
	}
	
	private function prepareDatasets() {
		$out = [];
		foreach ($this->datasetDefs as $def) {
			$out[] = [];
		}
		return $out;
	}
	
	private function fetchPackedValues(array $point)
	{
		$output = [];
		foreach ($this->datasetDefs as $def) {
			$output[] = $point[$def];
		}
		return $output;
	}
	
	private function packageIntoDataset(array $datePoint, array $values)
	{
		$this->labels[] = $this->printableDatePoint($datePoint);
		foreach ($values as $k => $v) {
			$this->datasets[$k][] = $v;
		}
	}
	
	private function normalizeDatePoint($datePoint)
	{
		$components = explode('-', $datePoint);
		$components[0] = (int) $components[0];
		$components[1] = (int) ltrim($components[1], '0');
		$components[2] = (int) ltrim($components[2], '0');
		return $components;
	}
	
	private function isSame(array $dp1, array $dp2)
	{
		return ($dp1[0] == $dp2[0] && $dp1[1] == $dp2[1] && $dp1[2] == $dp2[2]);
	}
	
	private function nextDatePoint(array $datePoint)
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
	
	private function printableDatePoint(array $datePoint)
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
