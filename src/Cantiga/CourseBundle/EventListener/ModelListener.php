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
namespace Cantiga\CourseBundle\EventListener;

use Cantiga\CoreBundle\Event\AreaEvent;
use Cantiga\CoreBundle\Event\ProjectCreatedEvent;
use Cantiga\CoreBundle\Settings\Setting;
use Cantiga\CourseBundle\CourseSettings;
use Cantiga\CourseBundle\Entity\CourseProgress;
use Doctrine\DBAL\Connection;

/**
 * Listens to the notifications about the model changes: project, area, etc.
 * and performs additional tasks.
 *
 * @author Tomasz Jędrzejewski
 */
class ModelListener
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
		$settings->create(new Setting(CourseSettings::MIN_QUESTION_NUM, 'Minimum number of questions in test', 'course', 10, Setting::TYPE_INTEGER));
	}
	
	public function onAreaCreated(AreaEvent $event)
	{
		$courseProgress = new CourseProgress($event->getArea());
		$courseProgress->insert($this->conn);
	}
}
