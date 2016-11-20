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
namespace Cantiga\Metamodel\Capabilities;

use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * @author Tomasz Jędrzejewski
 */
interface StatsInterface
{
	/**
	 * Returns the chart name
	 * 
	 * @return string
	 */
	public function getTitle();
	/**
	 * Performs the data collection and preparation. The method shall return true, if the statistics
	 * should be shown, false otherwise.
	 * 
	 * @param IdentifiableInterface $root Reference entity that can be used for preparing the statistics.
	 * @return boolean
	 */
	public function collectData(IdentifiableInterface $root);
	/**
	 * The method shall return a rendered view of the HTML placeholder
	 * for displaying the statistics.
	 * 
	 * @return string
	 */
	public function renderPlaceholder(TwigEngine $tpl);
	
	/**
	 * The method shall return a rendered view of the JavaScript code
	 * that prepares the chart and contains the data set.
	 * 
	 * @return string
	 */
	public function renderStatistics(TwigEngine $tpl);
}
