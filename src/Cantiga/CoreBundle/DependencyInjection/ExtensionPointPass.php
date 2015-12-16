<?php
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
