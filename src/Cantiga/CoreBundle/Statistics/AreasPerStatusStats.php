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
namespace Cantiga\CoreBundle\Statistics;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\StatsInterface;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * @author Tomasz Jędrzejewski
 */
class AreasPerStatusStats implements StatsInterface
{
	/**
	 * @var Connection
	 */
	private $conn;
	
	private $data;
	
	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}
	
	public function collectData(IdentifiableInterface $root)
	{
		$statuses = $this->conn->fetchAll('SELECT `id`, `name` FROM `'.CoreTables::AREA_STATUS_TBL.'` WHERE `projectId` = :projectId ORDER BY `name`', [':projectId' => $root->getId()]);
		
		if (sizeof($statuses) == 0) {
			return false;
		}
		
		$ids = [];
		$map = [];
		$i = 0;
		foreach ($statuses as &$status) {
			$status['count'] = 0;
			$ids[] = $status['id'];
			$map[$status['id']] = $i++;
		}
		
		$counts = $this->conn->fetchAll('SELECT `statusId`, COUNT(`id`) AS `cnt` FROM `'.CoreTables::AREA_TBL.'` WHERE `statusId` IN('.implode(',', $ids).') GROUP BY `statusId`');
		foreach($counts as $count) {
			$statuses[$map[$count['statusId']]]['count'] = $count['cnt'];
		}
		$this->data = $statuses;
		return true;
	}
	
	public function getTitle()
	{
		return 'Areas with given status';
	}

	public function renderPlaceholder(TwigEngine $tpl)
	{
		return $tpl->render('CantigaCoreBundle:Stats:area-per-status.html.twig');
	}

	public function renderStatistics(TwigEngine $tpl)
	{
		$labels = [];
		$data = [];
		foreach ($this->data as $item) {
			$labels[] = '"'.addslashes($item['name']).'"';
			$data[] = $item['count'];
		}
		return $tpl->render('CantigaCoreBundle:Stats:area-per-status.js.twig', array(
			'labels' => implode(', ', $labels),
			'data' => implode(', ', $data))
		);
	}
}
