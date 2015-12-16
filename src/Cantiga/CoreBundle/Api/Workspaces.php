<?php
namespace Cantiga\CoreBundle\Api;

use RuntimeException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Available workspaces are defined in the bundle. This class serves as a static dictionary for the
 * rest of the application.
 *
 * @author Tomasz Jędrzejewski
 */
class Workspaces
{
	const HIDDEN = true;
	
	private static $workspaces = array();
	
	public static function registerWorkspace($id, $title, $homePage, $role, $skin, $hidden = false)
	{
		self::$workspaces[$id] = ['id' => $id, 'title' => $title, 'homePage' => $homePage, 'role' => $role, 'skin' => $skin, 'hidden' => $hidden];
	}
	
	public static function get($id)
	{
		if (!isset(self::$workspaces[$id])) {
			throw new RuntimeException('No such workspace.');
		}
		return self::$workspaces[$id];
	}
	
	public static function fetchAll()
	{
		return self::$workspaces;
	}
	
	public static function fetchByRole(AuthorizationCheckerInterface $authCheck)
	{
		$result = array();
		foreach (self::$workspaces as $workspace) {
			if ($authCheck->isGranted($workspace['role'])) {
				$result[] = $workspace;
			}
		}
		return $result;
	}
	
	public static function getFormEntries()
	{
		$data = array();
		foreach (self::$workspaces as $id => $w) {
			$data[$id] = $w['title'];
		}
		return $data;
	}
}
