<?php
namespace Cantiga\CoreBundle\Block;

/**
 * @author Tomasz Jędrzejewski
 */
interface BlockLauncherInterface
{
	public function launchBlock($string, array $args = []);
}
