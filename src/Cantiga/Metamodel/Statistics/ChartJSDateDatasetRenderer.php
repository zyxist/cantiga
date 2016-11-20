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
 * Renders the data from {@link StatTimelineEngine} into the format understood by
 * ChartJS library.
 *
 * @author Tomasz Jędrzejewski
 */
class ChartJSDateDatasetRenderer
{
	private $datasets = [];
	
	/**
	 * Configures the next data set.
	 * 
	 * @param string $label Label of the next dataset.
	 * @param string $color Color given in the RGB notation: 230,230,230
	 * @return ChartJSDateDatasetRenderer 
	 */
	public function data($label, $color)
	{
		$this->datasets[] = ['label' => $label, 'color' => $color];
		return $this;
	}
	
	/**
	 * Generates a JSON code with the data description for ChartJS stacked
	 * bar chart.
	 * 
	 * @param \Cantiga\Metamodel\Statistics\StatDateDataset $engine
	 * @return string
	 */
	public function generateData(StatDateDataset $engine)
	{
		$output = [
			'labels' => $engine->getLabels(),
			'datasets' => []			
		];
		
		foreach ($this->datasets as $k => $dataset) {
			$output['datasets'][$k] = [
				'label' => $dataset['label'],
				'fillColor' => 'rgba('.$dataset['color'].',0.5)',
				'strokeColor' => 'rgba('.$dataset['color'].',0.8)',
				'highlightFill' => 'rgba('.$dataset['color'].',0.75)',
				'highlightStroke' => 'rgba('.$dataset['color'].',1)',
				'data' => $engine->getDataset($k)
			];
		}
		return json_encode($output);
	}
}
