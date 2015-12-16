<?php
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
