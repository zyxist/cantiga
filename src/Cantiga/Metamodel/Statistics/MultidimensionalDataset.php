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
 * Allows viewing the temporal dataset (the same, as recognized by {@link StatDateDataset})
 * in two dimensions: time vs category. It can be used for drawing a table N x M rows presenting
 * individual values for each date/category point.
 *
 * @author Tomasz Jędrzejewski
 */
class MultidimensionalDataset extends AbstractDataset
{
	private $categoryDimension = [];
	private $temporalDimension = [];
	private $revTemporalDimension = [];
	private $data = [];
	
	private $categoryFieldName;
	private $dataFieldName;
	
	/**
	 * The multidimensional dataset requires the names of two fields in the rows retrieved
	 * from the database that contain the category ID and values to be plotted, respecitvely.
	 * 
	 * @param string $categoryFieldName
	 * @param string $dataFieldName
	 */
	public function __construct($categoryFieldName, $dataFieldName)
	{
		$this->categoryFieldName = $categoryFieldName;
		$this->dataFieldName = $dataFieldName;
	}
	
	/**
	 * Adds a new category to the category dimension.
	 * 
	 * @param int $id Category ID
	 * @param array $data Category data
	 * @return \Cantiga\Metamodel\Statistics\MultidimensionalDataset
	 */
	public function addCategory($id, array $data)
	{
		$this->categoryDimension[$id] = $data;
		return $this;
	}
	
	public function getTemporalDimension()
	{
		return $this->temporalDimension;
	}
	
	public function getCategoryDimension()
	{
		return $this->categoryDimension;
	}
	
	public function countTemporalDimension()
	{
		return sizeof($this->temporalDimension);
	}
	
	public function getPointsFor($categoryId)
	{
		if (!isset($this->data[$categoryId])) {
			return [];
		}
		return $this->data[$categoryId];
	}
	
	public function process(array $data)
	{
		if (($size = sizeof($data)) == 0) {
			return;
		}
		$firstDatePoint = $data[0]['datePoint'];
		$lastDatePoint = $data[$size - 1]['datePoint'];
		
		list($td, $rtd) = $this->prepareTemporalDimension($firstDatePoint, $lastDatePoint);
		$this->temporalDimension = $td;
		$this->revTemporalDimension = $rtd;
		$this->data = $this->makeDataArray();
		
		foreach ($this->categorize($data) as $singleCategoryTimeline) {
			$this->processPacked($singleCategoryTimeline);
		}
		$this->removeNullValues();
		$points = sizeof($this->temporalDimension);
		uksort($this->categoryDimension, function($a, $b) use($points) {
			return $this->data[$b][$points - 1] - $this->data[$a][$points - 1];
		});
		
		return $this;
	}
	
	protected function packageIntoDataset(array $datePoint, array $values)
	{
		$this->data[$values[$this->categoryFieldName]][$this->revTemporalDimension[$this->printableDatePoint($datePoint)]] = $values[$this->dataFieldName];
	}
	
	protected function fetchPackedValues(array $point)
	{
		return [
			$this->categoryFieldName => $point[$this->categoryFieldName],
			$this->dataFieldName => $point[$this->dataFieldName]
		];
	}
	
	private function prepareTemporalDimension($firstDatePoint, $lastDatePoint)
	{
		$p = $this->normalizeDatePoint($firstDatePoint);
		$l = $this->normalizeDatePoint($lastDatePoint);
		$temporalDimension = [];
		$revTemporalDimension = [];
		$i = 0;
		while (!$this->isSame($p, $l)) {
			$printableDatePoint = $this->printableDatePoint($p);
			$temporalDimension[$i] = $printableDatePoint;
			$revTemporalDimension[$printableDatePoint] = $i;
			
			$i++;
			$p = $this->nextDatePoint($p);
		}
		$printableDatePoint = $this->printableDatePoint($l);
		$temporalDimension[$i] = $printableDatePoint;
		$revTemporalDimension[$printableDatePoint] = $i;
		return [$temporalDimension, $revTemporalDimension];
	}
	
	private function categorize(array $rawData)
	{
		$categorizedData = [];
		foreach ($rawData as $row) {
			if (!isset($categorizedData[$row[$this->categoryFieldName]])) {
				$categorizedData[$row[$this->categoryFieldName]] = [];
			}
			$categorizedData[$row[$this->categoryFieldName]][] = $row;
		}
		return $categorizedData;
	}
	
	private function makeDataArray()
	{
		$data = [];
		foreach ($this->categoryDimension as $i => $category) {
			foreach ($this->temporalDimension as $j => $datePoint) {
				$data[$i][$j] = null;
			}
		}
		return $data;
	}
	
	private function removeNullValues()
	{
		foreach ($this->data as $i => &$timeline) {
			$last = 0;
			foreach ($timeline as $j => &$value) {
				if (null === $value) {
					$value = $last;
				} else {
					$last = $value;
				}
			}
		}
	}
}
