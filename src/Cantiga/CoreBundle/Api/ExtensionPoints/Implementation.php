<?php
namespace Cantiga\CoreBundle\Api\ExtensionPoints;

/**
 * Storage for the information about available implementations.
 *
 * @author Tomasz Jędrzejewski
 */
class Implementation
{
	/**
	 * Name of an extension point
	 * @var string
	 */
	private $extensionPoint;
	/**
	 * Name of a module
	 * @var string
	 */
	private $module;
	/**
	 * Name of a service
	 * @var string
	 */
	private $service;
	/**
	 * Human-readable name
	 * @var string
	 */
	private $name;
	
	public function __construct($extensionPoint, $module, $service, $name)
	{
		$this->extensionPoint = $extensionPoint;
		$this->module = $module;
		$this->service = $service;
		$this->name = $name;
	}
	
	public function getExtensionPoint()
	{
		return $this->extensionPoint;
	}

	public function getModule()
	{
		return $this->module;
	}

	public function getService()
	{
		return $this->service;
	}
	
	public function getName()
	{
		return $this->name;
	}
}
