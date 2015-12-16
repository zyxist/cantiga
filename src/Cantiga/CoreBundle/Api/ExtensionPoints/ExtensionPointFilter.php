<?php
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
 * 
 * @author Tomasz Jędrzejewski
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
	public function withModules(array $modules)
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
	public function withServices(array $services)
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
	public function fromSettings(ProjectSettings $settings, $key)
	{
		return $this->withServices([$settings->get($key)->getValue()]);
	}
	
	/**
	 * Checks whether the given implementation matches the filter.
	 * 
	 * @param Implementation $implementation
	 * @return boolean
	 */
	public function matches(Implementation $implementation)
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
}
