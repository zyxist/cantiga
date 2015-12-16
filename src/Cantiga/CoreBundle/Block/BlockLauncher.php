<?php
namespace Cantiga\CoreBundle\Block;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Container;

/**
 * Description of BlockLauncher
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
