<?php
namespace Cantiga\CoreBundle\Api;

/**
 * Allows different bundles to register application text keys used in the given bundle,
 * so that it is easier to pick up the right name in the control panel.
 *
 * @author Tomasz Jędrzejewski
 */
class AppTexts
{
	private static $names = array();
	
	public static function registerName($name)
	{
		self::$names[$name] = $name;
	}
	
	public static function getNames()
	{
		return self::$names;
	}
}
