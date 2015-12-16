<?php
namespace Cantiga\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers the repositories that support membership functionality,
 * within the invitation repository so that it can redirect the actual
 * invitation approval to the appropriate entity.
 *
 * @author Tomasz Jędrzejewski
 */
class MembershipPass implements CompilerPassInterface
{
	public function process(ContainerBuilder $container)
	{
		if (!$container->has('cantiga.core.repo.invitation')) {
            return;
        }
		$definition = $container->findDefinition('cantiga.core.repo.invitation');
		$taggedServices = $container->findTaggedServiceIds('cantiga.invitation-aware');
		
		foreach ($taggedServices as $id => $tags) {
			foreach ($tags as $attr) {
				$definition->addMethodCall('registerRepository', [$attr['entity'], new Reference($id)]);
			}
		}
	}
}
