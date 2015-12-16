<?php
namespace Cantiga\CoreBundle\Settings;

use Doctrine\DBAL\Connection;
use PDO;
use Cantiga\CoreBundle\CoreTables;

/**
 * Description of DatabaseSettingsStorage
 *
 * @author Tomasz Jędrzejewski
 */
class DatabaseSettingsStorage implements SettingsStorageInterface
{
	/**
	 * @var Connection
	 */
	private $conn;
	
	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}
	
	public function loadSettings($projectId)
	{
		$stmt = $this->conn->prepare('SELECT * FROM `'.CoreTables::PROJECT_SETTINGS_TBL.'` WHERE `projectId` = :id');
		$stmt->bindValue(':id', $projectId);
		$stmt->execute();
		
		$result = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$result[] = new Setting($row['key'], $row['name'], $row['module'], $row['value'], $row['type'], $row['extensionPoint']);
		}
		$stmt->closeCursor();
		
		return $result;
	}

	public function saveSettings($projectId, array $settings)
	{
		$stmt = $this->conn->prepare('INSERT INTO `'.CoreTables::PROJECT_SETTINGS_TBL.'` (`projectId`, `key`, `name`, `module`, `value`, `type`, `extensionPoint`) '
			. 'VALUES(:projectId, :key, :name, :module, :value, :type, :extensionPoint) ON DUPLICATE KEY UPDATE `value` = :newValue');
		
		foreach ($settings as $setting) {
			$stmt->bindValue(':projectId', $projectId);
			$stmt->bindValue(':key', $setting->getKey());
			$stmt->bindValue(':name', $setting->getName());
			$stmt->bindValue(':module', $setting->getModule());
			$stmt->bindValue(':value', $setting->getNormalizedValue());
			$stmt->bindValue(':type', $setting->getType());
			$stmt->bindValue(':extensionPoint', $setting->getExtensionPoint());
			$stmt->bindValue(':newValue', $setting->getNormalizedValue());
			$stmt->execute();
		}
	}
}
