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
namespace Cantiga\CoreBundle\Settings;

/**
 * Specifies, how the project settings are persisted.
 * 
 * @author Tomasz Jędrzejewski
 */
interface SettingsStorageInterface
{
	/**
	 * The method shall return an unordered array of {@link Setting} instances.
	 * 
	 * @param int $projectId
	 * @return array
	 */
	public function loadSettings($projectId);
	/**
	 * The method gets the project ID and the array of {@link Setting} instances
	 * and it is supposed to persist them. The method is expected to be called only
	 * by the settings editor.
	 * 
	 * @param int $projectId
	 * @param array $settings
	 */
	public function saveSettings($projectId, array $settings);
}
