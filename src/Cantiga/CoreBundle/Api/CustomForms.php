<?php
namespace Cantiga\CoreBundle\Api;

/**
 * Allows different bundles to register custom form implementations, so that they can be
 * attached to certain places within the system.
 *
 * @author Tomasz Jędrzejewski
 */
class CustomForms
{
	private static $services = array();
	
	public static function registerService($name, $implementation)
	{
		self::$services[$name] = $implementation;
	}
	
	public static function getNames()
	{
		return self::$services;
	}
	
	public static function getSelectableNames()
	{
		$result = array();
		foreach (self::$services as $name => $impl) {
			$result[$name] = $name;
		}
		return $result;
	}
	
	public static function asService($name)
	{
		if (!isset(self::$services[$name])) {
			throw new \LogicException('The specified custom form does not exist: '.$name);
		}
		return self::$services[$name];
	}
}
