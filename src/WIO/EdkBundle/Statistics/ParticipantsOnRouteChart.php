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
use Cantiga\Metamodel\Statistics\ChartJSDateDatasetRenderer;
use Cantiga\Metamodel\Statistics\PaletteGenerator;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Translation\TranslatorInterface;
use WIO\EdkBundle\Entity\WhereLearntAbout;
use WIO\EdkBundle\Repository\EdkParticipantRepository;

/**
 * Displays the distribution of participants between individual routes.
 *
 * @author Tomasz Jędrzejewski
 */
class ParticipantsOnRouteChart implements StatsInterface
{
	/**
	 * @var CoreStatisticsRepository
	 */
	private $repo;
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
		$this->data = $this->enhanceData($this->repo->countParticipantsOnRoutes($root));
		return true;
	}

	public function getTitle()
	{
		return $this->translator->trans('Distribution of participants on routes', [], 'edk');
	}

	public function renderPlaceholder(TwigEngine $tpl)
	{
		return $tpl->render('WioEdkBundle:Stats:participants-on-route-chart.html.twig');
	}

	public function renderStatistics(TwigEngine $tpl)
	{
		return $tpl->render('WioEdkBundle:Stats:participants-on-route-chart.js.twig', array(
			'data' => json_encode($this->data),
		));
	}
	
	private function enhanceData($data)
	{
		$paletteGen = new PaletteGenerator();
		$palette = $paletteGen->generatePalette(sizeof($data), 138, 86, 226);
		
		$chartData = [];
		$i = 0;
		foreach ($data as $route) {
			$color = $palette[$i++];
			$chartData[] = [
				'label' => $route['name'],
				'value' => $route['participantNum'],
				'color' => $color['c'],
				'highlight' => $color['h'],
			];
		}
		return $chartData;
	}
}
