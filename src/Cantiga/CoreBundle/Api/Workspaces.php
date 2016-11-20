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
