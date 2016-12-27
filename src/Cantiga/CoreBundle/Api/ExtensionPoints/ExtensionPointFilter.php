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

use Cantiga\CoreBundle\Settings\ProjectSettings;

/**
 * Filter that limits the available services that implement the particular extension
 * point, by some criteria. The default criteria are:
 * 
 * <ul>
 *  <li>only implementations for certain modules,</li>
 *  <li>only services from the specified set,</li>
 * </ul>
 *
 * <p>The instances of this class are immutable. Every new invocation that is expected
 * to change the state, actually produces a new instance, leaving the previous one untouched.
 */
class ExtensionPointFilter
{
	private $modules = array();
	private $services = array();
	private $allModules = true;
	private $allServices = true;
	
	public function __construct(array $modules = [])
	{
		if (sizeof($modules) > 0) {
			$this->allModules = false;
			$this->modules = array_merge($this->modules, $modules);
		}		
	}
	
	/**
	 * Defines a filter for extension point implementations, which allows only those
	 * implementations that belong to certain modules.
	 * 
	 * @param array $modules List of allowed modules
	 * @return ExtensionPointFilter
	 */
	public function withModules(array $modules): self
	{
		$newInstance = new ExtensionPointFilter();
		$newInstance->modules = array_merge($this->modules, $modules);
		$newInstance->allModules = false;
		$newInstance->services = $this->services;
		$newInstance->allServices = $this->allServices;
		return $newInstance;
	}
	
	/**
	 * Defines a filter for extension point implementations, which allows only those
	 * services that belong to a particular set.
	 * 
	 * @param array $services List of allowed services.
	 * @return ExtensionPointFilter
	 */
	public function withServices(array $services): self
	{
		$newInstance = new ExtensionPointFilter();
		$newInstance->modules = $this->modules;
		$newInstance->allModules = $this->allModules;
		$newInstance->services = array_merge($this->services, $services);
		$newInstance->allServices = false;
		return $newInstance;
	}
	
	/**
	 * Defines a filter for extension point implementations, which allows only the specified
	 * service, which is read from project settings
	 * 
	 * @param ProjectSettings $settings Settings storage
	 * @param string $key setting name
	 * @return ExtensionPointFilter
	 */
	public function fromSettings(ProjectSettings $settings, string $key): self
	{
		return $this->withServices([$settings->get($key)->getValue()]);
	}
	
	/**
	 * Checks whether the given implementation matches the filter.
	 * 
	 * @param Implementation $implementation
	 * @return boolean
	 */
	public function matches(Implementation $implementation): bool
	{
		if (!$this->allModules) {
			if (!in_array($implementation->getModule(), $this->modules)) {
				return false;
			}
		}
		if (!$this->allServices) {
			if (!in_array($implementation->getService(), $this->services)) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Checks whether the given module is supported.
	 * 
	 * @param string $moduleName
	 */
	public function matchesModule(string $moduleName): bool
	{
		if (!$this->allModules) {
			return in_array($moduleName, $this->modules);
		}
		return true;
	}
	
	public function getModules(): array
	{
		return $this->modules;
	}
	
	public function getServices(): array
	{
		return $this->services;
	}
}
