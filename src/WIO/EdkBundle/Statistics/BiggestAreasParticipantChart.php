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

use Cantiga\CoreBundle\Repository\CoreStatisticsRepository;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\StatsInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Translation\TranslatorInterface;
use WIO\EdkBundle\Repository\EdkParticipantRepository;

/**
 * Shows the biggest areas in the terms of participant number.
 *
 * @author Tomasz Jędrzejewski
 */
class BiggestAreasParticipantChart implements StatsInterface
{
	const SHOW_MAX = 10;
	/**
	 * @var CoreStatisticsRepository
	 */
	private $repo;
	/**
	 * Loaded data
	 * @var array
	 */
	private $data;
	/**
	 * @var TranslatorInterface
	 */
	private $translator;
	
	public function __construct(EdkParticipantRepository $repository, TranslatorInterface $translator)
	{
		$this->repo = $repository;
		$this->translator = $translator;
	}
	
	public function collectData(IdentifiableInterface $root)
	{
		$this->data = $this->repo->fetchBiggestAreasByParticipants($root, self::SHOW_MAX);
		return true;
	}

	public function getTitle()
	{
		return $this->translator->trans('0 biggest areas (by participants)', [self::SHOW_MAX], 'edk');
	}

	public function renderPlaceholder(TwigEngine $tpl)
	{
		return $tpl->render('WioEdkBundle:Stats:biggest-areas-chart.html.twig');
	}

	public function renderStatistics(TwigEngine $tpl)
	{
		$labels = [];
		$data = [];
		foreach ($this->data as $area) {
			$labels[] = '\''.addslashes($area['name']).'\'';
			$data[] = $area['sum'];
		}
		
		return $tpl->render('WioEdkBundle:Stats:biggest-areas-chart.js.twig', array(
			'labels' => implode(',', $labels),
			'data' => implode(',', $data),
		));
	}
}
