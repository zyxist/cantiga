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
namespace Cantiga\CoreBundle\Block;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Container;

/**
 * Originally made as a lightweight replacement for embedding controllers in templates
 * and customizing pages such as dashboards, but currently it is rarely used and probably
 * will be removed. To be decided.
 *
 * @author Tomasz Jędrzejewski
 */
class BlockLauncher implements BlockLauncherInterface
{
	private $container;
	
	public function __construct(Container $container)
	{
		$this->container = $container;
	}
	
	public function launchBlock($string, array $args = array())
	{
		$parts = explode(':', $string);
		
		if (sizeof($parts) != 2) {
			throw new InvalidArgumentException('Invalid block reference: \''.$string.'\'');
		}
		
		$service = $this->container->get($parts[0]);
		$method = $parts[1].'Action';
		if (method_exists($service, $method)) {
			return $service->$method($args);
		}
		throw new InvalidArgumentException('Unknown block action: \''.$parts[1].'\' in block \''.$parts[0].'\'');
	}
}
