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

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use LogicException;
use Cantiga\CoreBundle\Entity\Project;

/**
 * Storage for configurable settings of the project. The settings can be edited directly from
 * the application. When a new project is created, the modules shall inject the necessary settings
 * by listening for a project creation event, which are later persisted as default values.
 * 
 * <p>During the normal usage, the settings are lazily-loaded.
 *
 * @author Tomasz Jędrzejewski
 */
class ProjectSettings implements IteratorAggregate
{
	/**
	 * @var SettingsStorageInterface 
	 */
	private $storage;
	/**
	 * @var Project
	 */
	private $project;
	/**
	 * @var array
	 */
	private $settings = null;
	
	public function __construct(SettingsStorageInterface $storage)
	{
		$this->storage = $storage;
	}
	
	public function setProject(Project $project)
	{
		$this->project = $project;
	}
	
	/**
	 * @return Project
	 */
	public function getProject()
	{
		return $this->project;
	}
	
	/**
	 * Adds a new setting to the project. This method is expected to be used during the
	 * project creation of application expansion.
	 * 
	 * @param Setting $setting
	 * @return ProjectSettings
	 * @throws InvalidArgumentException
	 */
	public function create(Setting $setting)
	{
		if (isset($this->settings[$setting->getKey()])) {
			throw new InvalidArgumentException('Duplicate settings key: \''.$key.'\'');
		}
		$this->settings[$setting->getKey()] = $setting;
		return $this;
	}
	
	/**
	 * Fetches the given setting. An exception is thrown if the setting does not exist.
	 * 
	 * @param string $key
	 * @return Setting
	 * @throws InvalidArgumentException
	 */
	public function get($key)
	{
		if (null == $this->settings) {
			$this->loadSettings();
		}
		if (!isset($this->settings[$key])) {
			throw new InvalidArgumentException('Invalid settings key: \''.$key.'\'');
		}
		return $this->settings[$key];
	}
	
	private function loadSettings()
	{
		if (null === $this->project) {
			throw new LogicException('Cannot load project settings: project not loaded yet.');
		}
		foreach($this->storage->loadSettings($this->project->getId()) as $setting) {
			if (isset($this->settings[$setting->getKey()])) {
				throw new LogicException('Duplicate project settings key: \''.$setting->getKey().'\'');
			}
			$this->settings[$setting->getKey()] = $setting;
		}
	}
	
	/**
	 * Retrieves the settings grouped by modules. The returned value is an array, where
	 * the key is the module name, and value a sub-array with individual settings for
	 * this module. The settings are sorted by keys.
	 * 
	 * @return array
	 */
	public function getOrganized() {
		$organized = array();
		foreach ($this->settings as $setting) {
			if ($this->project->supports($setting)) {
				if (!isset($organized[$setting->getModule()])) {
					$organized[$setting->getModule()] = array();
				}
				$organized[$setting->getModule()][] = $setting;
			}
		}

		ksort($organized);
		foreach ($organized as &$moduleSettings) {
			usort($moduleSettings, function($a, $b) {
				return strcmp($a->getKey(), $b->getKey());
			});
		}

		return $organized;
	}
	
	/**
	 * Converts the settings structure into an array, where the key is the setting name,
	 * and the value is the setting value.
	 * 
	 * @return array
	 */
	public function toArray()
	{
		if (null == $this->settings) {
			$this->loadSettings();
		}
		$array = array();
		foreach ($this->settings as $setting) {
			$array[$setting->getKey()] = $setting->getValue();
		}
		return $array;
	}
	
	public function fromArray(array $array)
	{
		foreach ($array as $key => $value) {
			$this->get($key)->setValue($value);
		}
	}
	
	
	public function saveSettings()
	{
		$this->storage->saveSettings($this->project->getId(), $this->settings);
	}

	public function getIterator()
	{
		return new ArrayIterator($this->settings);
	}
}
