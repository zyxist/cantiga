<?php
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
