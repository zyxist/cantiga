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
 * Displays the distribution of the source of knowledge about EDK.
 *
 * @author Tomasz Jędrzejewski
 */
class ParticipantWhereLearntChart implements StatsInterface
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
		$this->data = $this->enhanceData($this->repo->countWhereLearntGrouped($root));
		return true;
	}

	public function getTitle()
	{
		return $this->translator->trans('Where learnt about Extreme Way?', [], 'edk');
	}

	public function renderPlaceholder(TwigEngine $tpl)
	{
		return $tpl->render('WioEdkBundle:Stats:where-learnt-chart.html.twig');
	}

	public function renderStatistics(TwigEngine $tpl)
	{
		return $tpl->render('WioEdkBundle:Stats:where-learnt-chart.js.twig', array(
			'data' => json_encode($this->data),
		));
	}
	
	private function enhanceData($data)
	{
		$options = WhereLearntAbout::getItems();
		$paletteGen = new PaletteGenerator();
		$palette = $paletteGen->generatePalette(sizeof($options), 138, 86, 226);
		
		$chartData = [];
		foreach ($options as $option) {
			if (!empty($data[$option->getId()])) {
				$color = $this->chooseColor($palette, $option->getId());
				$chartData[] = [
					'value' => $data[$option->getId()],
					'label' => $this->translator->trans($option->getName(), [], 'edk'),
					'color' => $color['c'],
					'highlight' => $color['h']
				];
			}
		}
		return $chartData;
	}
	
	private function chooseColor($palette, $id)
	{
		if ($id == 100) {
			return $palette[0];
		}
		return $palette[$id];
	}
}
