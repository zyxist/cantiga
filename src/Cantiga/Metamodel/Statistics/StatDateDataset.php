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

use Cantiga\CoreBundle\Exception\ChartException;

/**
 * Prepares the statistical data to be displayed on a bar chart, where the X axis is the
 * timeline. The implementation assumes sparse storage of individual dates in the database,
 * and fills in the holes with the last known point of measure.
 *
 * @author Tomasz Jędrzejewski
 */
class StatDateDataset extends AbstractDataset
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
	
	protected function beforePacking()
	{
		$this->labels = [];
		$this->datasets = [];
		foreach ($this->datasetDefs as $def) {
			$this->datasets[] = [];
		}
	}
	
	protected function fetchPackedValues(array $point)
	{
		$output = [];
		foreach ($this->datasetDefs as $def) {
			$output[] = $point[$def];
		}
		return $output;
	}
	
	protected function packageIntoDataset(array $datePoint, array $values)
	{
		$this->labels[] = $this->printableDatePoint($datePoint);
		foreach ($values as $k => $v) {
			$this->datasets[$k][] = $v;
		}
	}
}
