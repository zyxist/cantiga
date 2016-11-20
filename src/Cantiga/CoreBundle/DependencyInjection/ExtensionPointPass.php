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

namespace Cantiga\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Registers the implementations within the extension points.
 *
 * @author Tomasz Jędrzejewski
 */
class ExtensionPointPass implements CompilerPassInterface
{

	public function process(ContainerBuilder $container)
	{
		if (!$container->has('cantiga.extensions')) {
			return;
		}
		$definition = $container->findDefinition('cantiga.extensions');
		$taggedServices = $container->findTaggedServiceIds('cantiga.extension');
		foreach ($taggedServices as $id => $tags) {
			foreach ($tags as $attr) {
				$args = [$attr['point']];
				if (isset($attr['module'])) {
					$args[] = $attr['module'];
				} else {
					$args[] = 'core';
				}
				$args[] = $id;
				if (isset($attr['description'])) {
					$args[] = $attr['description'];
				} else {
					$args[] = $id;
				}
				$definition->addMethodCall('registerFromArgs', $args);
			}
		}
	}

}
