<?php
namespace Cantiga\CoreBundle\EventListener;

use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\CoreSettings;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Event\ProjectCreatedEvent;
use Cantiga\CoreBundle\Settings\Setting;
use Doctrine\DBAL\Connection;

/**
 * Handles the project creation and archivization by filling in the necessary tables.
 * Other bundles can hook into the same events and configure themselves, when a new
 * project is being created.
 *
 * @author Tomasz Jędrzejewski
 */
class ProjectListener
{
	/**
	 * @var Connection 
	 */
	private $conn;
	
	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}
	
	public function onProjectCreated(ProjectCreatedEvent $event)
	{
		$settings = $event->getSettings();
		$settings->create(new Setting(CoreSettings::AREA_NAME_HINT, 'Hint for the area name displayed in the area registration form', 'core', 'Sample area hint', Setting::TYPE_STRING));
		$settings->create(new Setting(CoreSettings::AREA_REQUEST_INFO_TEXT, 'Text displayed during area registration', 'core', 'Sample text', Setting::TYPE_STRING));
		$settings->create(new Setting(CoreSettings::AREA_REQUEST_FORM, 'Area request form', 'core', 'cantiga.core.form.default_area_request', Setting::TYPE_EXTENSION_POINT, CoreExtensions::AREA_REQUEST_FORM));
		$settings->create(new Setting(CoreSettings::AREA_FORM, 'Area form', 'core', 'cantiga.core.form.default_area', Setting::TYPE_EXTENSION_POINT, CoreExtensions::AREA_FORM));
	
		
		$this->conn->insert(CoreTables::AREA_STATUS_TBL, [
			'name' => 'New',
			'label' => 'primary',
			'isDefault' => 1,
			'projectId' => $event->getProject()->getId()
		]);
	}
}
