<?php
namespace Cantiga\CoreBundle\Api\ExtensionPoints;

use RuntimeException;
use Symfony\Component\DependencyInjection\Container;

/**
 * Manages the implementations for the extension points, where the bundles
 * can hook in to extend the default functionality. The extension points
 * are populated by inspecting tags in the <tt>services.yml</tt> files.
 *
 * @author Tomasz Jędrzejewski
 */
class ExtensionPoints implements ExtensionPointsInterface
{
	/**
	 * @var Container
	 */
	private $container;
	/**
	 * @var array
	 */
	private $extensionPoints;
	
	public function __construct(Container $container)
	{
		$this->container = $container;
	}
	
	public function register(Implementation $impl)
	{
		if (!isset($this->extensionPoints[$impl->getExtensionPoint()])) {
			$this->extensionPoints[$impl->getExtensionPoint()] = [];
		}
		$this->extensionPoints[$impl->getExtensionPoint()][] = $impl;
	}
	
	public function registerFromArgs($extensionPoint, $module, $service, $name = '')
	{
		$this->register(new Implementation($extensionPoint, $module, $service, $name));
	}
	
	public function describeImplementations($extPointName, ExtensionPointFilter $filter)
	{
		if (!isset($this->extensionPoints[$extPointName])) {
			return array();
		}
		$results = array();
		foreach ($this->extensionPoints[$extPointName] as $impl) {
			if ($filter->matches($impl)) {
				$results[$impl->getService()] = $impl->getName();
			}
		}
		return $results;
	}

	public function findImplementations($extPointName, ExtensionPointFilter $filter)
	{
		if (!isset($this->extensionPoints[$extPointName])) {
			return false;
		}
		$results = array();
		foreach ($this->extensionPoints[$extPointName] as $impl) {
			if ($filter->matches($impl)) {
				$results[] = $this->container->get($impl->getService());
			}
		}
		return $results;
	}

	public function getImplementation($extPointName, ExtensionPointFilter $filter)
	{
		if (!isset($this->extensionPoints[$extPointName])) {
			return false;
		}
		foreach ($this->extensionPoints[$extPointName] as $impl) {
			if ($filter->matches($impl)) {
				return $this->container->get($impl->getService());
			}
		}
		throw new RuntimeException('No implementation for an extension point \''.$extPointName.'\'');
	}

	public function hasImplementation($extPointName, ExtensionPointFilter $filter)
	{
		if (!isset($this->extensionPoints[$extPointName])) {
			return false;
		}
		foreach ($this->extensionPoints[$extPointName] as $impl) {
			if ($filter->matches($impl)) {
				return true;
			}
		}
		return false;
	}
}
