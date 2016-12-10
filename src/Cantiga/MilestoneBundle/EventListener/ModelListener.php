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
namespace Cantiga\MilestoneBundle\EventListener;

use Cantiga\CoreBundle\Event\AreaEvent;
use Cantiga\CoreBundle\Event\GroupEvent;
use Cantiga\CoreBundle\Event\ProjectCreatedEvent;
use Cantiga\CoreBundle\Settings\Setting;
use Cantiga\MilestoneBundle\Entity\Milestone;
use Cantiga\MilestoneBundle\Entity\MilestoneRule;
use Cantiga\MilestoneBundle\Event\ActivationEvent;
use Cantiga\MilestoneBundle\MilestoneSettings;
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
		$settings->create(new Setting(MilestoneSettings::AREA_CAN_UPDATE_OWN_PROGRESS, 'Can area update its own progress?', 'milestone', 0, Setting::TYPE_BOOLEAN));
		$settings->create(new Setting(MilestoneSettings::GROUP_CAN_UPDATE_OWN_PROGRESS, 'Can group update its own progress?', 'milestone', 0, Setting::TYPE_BOOLEAN));
		$settings->create(new Setting(MilestoneSettings::GROUP_CAN_UPDATE_AREA_PROGRESS, 'Can group update progress of areas?', 'milestone', 0, Setting::TYPE_BOOLEAN));
	}
	
	public function onGroupCreated(GroupEvent $event)
	{
		Milestone::populateEntities($this->conn, $event->getGroup()->getPlace(), $event->getGroup()->getProject());
	}
	
	public function onAreaCreated(AreaEvent $event)
	{
		Milestone::populateEntities($this->conn, $event->getArea()->getPlace(), $event->getArea()->getProject());
	}
	
	/**
	 * Handles the activations of the milestone rules, which trigger automatic change of the milestone
	 * status.
	 * 
	 * @param \Cantiga\MilestoneBundle\EventListener\ActivationEvent $event
	 */
	public function onActivated(ActivationEvent $event)
	{
		
		if ($event->getProject()->supportsModule('milestone')) {
			$rule = MilestoneRule::fetchByActivator($this->conn, $event->getProject(), $event->getActivator(), $event->getEntity());
			if (false !== $rule) {
				$rule->fireRule($this->conn, $event->getEntity(), $event->getCallback());
			}
		}
	}
}

