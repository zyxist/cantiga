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
namespace WIO\EdkBundle\Statistics;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\StatsInterface;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\TwigBundle\TwigEngine;
use WIO\EdkBundle\EdkTables;

/**
 * Displays the histogram of the route length.
 *
 * @author Tomasz Jędrzejewski
 */
class RouteLengthHistogram implements StatsInterface
{
	/**
	 * @var Connection
	 */
	private $conn;
	/**
	 * Pair length to count
	 * @var array
	 */
	private $data;
	
	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}
	
	public function collectData(IdentifiableInterface $root)
	{
		$routes = $this->conn->fetchAll('SELECT `routeLength` '
			. 'FROM `'.EdkTables::ROUTE_TBL.'` r '
			. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON r.`areaId` = a.`id` '
			. 'WHERE a.`projectId` = :projectId AND r.`routeType` = 0', [':projectId' => $root->getId()]);
		
		if (sizeof($routes) == 0) {
			return false;
		}
		
		$this->data = [];
		$min = 10000;
		$max = 0;
		foreach ($routes as $row) {
			if ($row['routeLength'] < $min) {
				$min = $row['routeLength'];
			}
			if ($row['routeLength'] > $max) {
				$max = $row['routeLength'];
			}
		}
		if ($min >= $max) {
			return false;
		}
		for ($i = $min; $i <= $max; $i++) {
			$this->data[$i] = 0;
		}
		
		foreach ($routes as $row) {
			$this->data[$row['routeLength']]++;
		}
		return true;
	}
	
	public function getTitle()
	{
		return 'RouteLengthHistogram';
	}

	public function renderPlaceholder(TwigEngine $tpl)
	{
		return $tpl->render('WioEdkBundle:Stats:route-length-histogram.html.twig');
	}

	public function renderStatistics(TwigEngine $tpl)
	{
		$labels = [];
		$data = [];
		foreach ($this->data as $length => $number) {
			$labels[] = '"'.$length.' km"';
			$data[] = $number;
		}
		return $tpl->render('WioEdkBundle:Stats:route-length-histogram.js.twig', array(
			'labels' => implode(', ', $labels),
			'data' => implode(', ', $data))
		);
	}
}
