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

use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\StatsInterface;
use Cantiga\Metamodel\Statistics\PaletteGenerator;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Translation\TranslatorInterface;
use WIO\EdkBundle\Repository\EdkRouteRepository;

/**
 * Displays the summary how many routes contain guides, and maps.
 *
 * @author Tomasz Jędrzejewski
 */
class RouteFilesChart implements StatsInterface
{
	/**
	 * @var EdkRouteRepository
	 */
	private $repo;
	/**
	 * @var TranslatorInterface
	 */
	private $translator;
	
	public function __construct(EdkRouteRepository $repository, TranslatorInterface $translator)
	{
		$this->repo = $repository;
		$this->translator = $translator;
	}

	public function collectData(IdentifiableInterface $root)
	{
		$this->data = $this->enhanceData($this->repo->countRouteFiles($root));
		return true;
	}

	public function getTitle()
	{
		return $this->translator->trans('Published route files', [], 'edk');
	}

	public function renderPlaceholder(TwigEngine $tpl)
	{
		return $tpl->render('WioEdkBundle:Stats:route-files-chart.html.twig');
	}

	public function renderStatistics(TwigEngine $tpl)
	{
		return $tpl->render('WioEdkBundle:Stats:route-files-chart.js.twig', array(
			'data' => json_encode($this->data),
		));
	}
	
	private function enhanceData($data)
	{
		$paletteGen = new PaletteGenerator();
		$palette = $paletteGen->generatePalette(sizeof($data), 138, 86, 226);
		
		$chartData = [];
		$i = 0;
		foreach ($data as $item) {
			$color = $palette[$i++];
			$chartData[] = [
				'label' => $this->translator->trans($item['name'], [], 'edk'),
				'value' => $item['value'],
				'color' => $color['c'],
				'highlight' => $color['h'],
			];
		}
		return $chartData;
	}
}
