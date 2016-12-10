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

namespace Cantiga\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers the services that are able to load single instances of certain places (e.g. projects).
 */
class PlaceLoaderPass implements CompilerPassInterface
{
	public function process(ContainerBuilder $container)
	{
		if (!$container->has('cantiga.user.membership.finder')) {
			return;
		}
		$definition = $container->findDefinition('cantiga.user.membership.finder');
		$taggedServices = $container->findTaggedServiceIds('cantiga.place-loader');

		foreach ($taggedServices as $id => $tags) {
			foreach ($tags as $attr) {
				$definition->addMethodCall('addPlaceLoader', [$attr['type'], new Reference($id)]);
			}
		}
	}
}
