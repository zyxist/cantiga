<?php
namespace Cantiga\CoreBundle\Api;

/**
 * Available modules are defined in the bundle. This class serves as a static dictionary for the
 * rest of the application.
 *
 * @author Tomasz Jędrzejewski
 */
class Modules
{
	private static $modules = array();
	
	public static function registerModule($id, $name)
	{
		self::$modules[$id] = ['id' => $id, 'name' => $name];
	}
	
	public static function get($id)
	{
		if (!isset(self::$modules[$id])) {
			throw new \RuntimeException('No such module.');
		}
		return self::$modules[$id];
	}
	
	public static function fetchAll()
	{
		return self::$modules;
	}
	
	public static function getFormEntries()
	{
		$data = array();
		foreach (self::$modules as $id => $w) {
			$data[$id] = $w['name'];
		}
		return $data;
	}
}
