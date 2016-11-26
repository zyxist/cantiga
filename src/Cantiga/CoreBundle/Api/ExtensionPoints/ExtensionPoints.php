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
declare(strict_types=1);
namespace Cantiga\CoreBundle\Api\ExtensionPoints;

use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manages the implementations for the extension points, where the bundles
 * can hook in to extend the default functionality. The extension points
 * are populated by inspecting tags in the <tt>services.yml</tt> files.
 */
class ExtensionPoints implements ExtensionPointsInterface
{
	/**
	 * @var ContainerInterface
	 */
	private $container;
	/**
	 * @var array
	 */
	private $extensionPoints;
	
	public function __construct(ContainerInterface $container)
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
	
	public function registerFromArgs(string $extensionPoint, string $module, string $service, string $name = '')
	{
		$this->register(new Implementation($extensionPoint, $module, $service, $name));
	}
	
	public function describeImplementations(string $extPointName, ExtensionPointFilter $filter): array
	{
		if (!isset($this->extensionPoints[$extPointName])) {
			return [];
		}
		$results = [];
		foreach ($this->extensionPoints[$extPointName] as $impl) {
			if ($filter->matches($impl)) {
				$results[$impl->getName()] = $impl->getService();
			}
		}
		return $results;
	}
	
	public function hasExtensionPoint(string $extPointName): bool
	{
		return isset($this->extensionPoints[$extPointName]);
	}

	public function findImplementations(string $extPointName, ExtensionPointFilter $filter): array
	{
		if (!isset($this->extensionPoints[$extPointName])) {
			return [];
		}
		$results = [];
		foreach ($this->extensionPoints[$extPointName] as $impl) {
			if ($filter->matches($impl)) {
				$results[] = $this->container->get($impl->getService());
			}
		}
		return $results;
	}

	public function getImplementation(string $extPointName, ExtensionPointFilter $filter)
	{
		if (!isset($this->extensionPoints[$extPointName])) {
			return false;
		}
		foreach ($this->extensionPoints[$extPointName] as $impl) {
			if ($filter->matches($impl)) {
				return $this->container->get($impl->getService());
			}
		}
		throw new RuntimeException('No implementation for the extension point \''.$extPointName.'\'');
	}

	public function hasImplementation(string $extPointName, ExtensionPointFilter $filter): bool
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
