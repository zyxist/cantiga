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
namespace WIO\EdkBundle\EventListener;

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Event\AreaEvent;
use Cantiga\CoreBundle\Event\ProjectCreatedEvent;
use Cantiga\CoreBundle\Settings\Setting;
use Cantiga\MilestoneBundle\Entity\NewMilestoneStatus;
use Cantiga\MilestoneBundle\Event\ActivationEvent;
use Cantiga\MilestoneBundle\MilestoneEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use WIO\EdkBundle\EdkSettings;

/**
 * Handles profile completeness and other stuff.
 *
 * @author Tomasz Jędrzejewski
 */
class ModelListener
{
	const REQUIRED_PROFILE_COMPLETENESS = 40;
	
	/**
	 * @var EventDispatcherInterface
	 */
	private $eventDispatcher;
	
	public function __construct(EventDispatcherInterface $eventDispatcher)
	{
		$this->eventDispatcher = $eventDispatcher;
	}
	
	public function onProjectCreated(ProjectCreatedEvent $event)
	{
		$settings = $event->getSettings();
		$settings->create(new Setting(EdkSettings::PUBLISHED_AREA_STATUS, 'Published area status ID', 'edk', 0, Setting::TYPE_INTEGER));
		$settings->create(new Setting(EdkSettings::GUIDE_MIRROR_URL, 'Guide mirror URL', 'edk', 0, Setting::TYPE_STRING));
		$settings->create(new Setting(EdkSettings::MAP_MIRROR_URL, 'Map mirror URL', 'edk', 0, Setting::TYPE_STRING));
		$settings->create(new Setting(EdkSettings::GPS_MIRROR_URL, 'GPS mirror URL', 'edk', 0, Setting::TYPE_STRING));
	}

	public function onAreaUpdated(AreaEvent $event)
	{
		$area = $event->getArea();
		$this->eventDispatcher->dispatch(MilestoneEvents::ACTIVATION_EVENT, new ActivationEvent($area->getProject(), $area->getPlace(), 'profile.updated', function() use($area) {
			return NewMilestoneStatus::create((bool) $this->canApproveProfile($area));
		}));
	}

	private function canApproveProfile(Area $area)
	{
		$data = $area->getCustomData();
		if (!empty($data['positionLat']) && !empty($data['positionLng']) && !empty($data['ewcDate']) && !empty($data['ewcDate']['year'])) {
			if ($area->getPercentCompleteness() > self::REQUIRED_PROFILE_COMPLETENESS) {
				return true;
			}
		}
		return false;
	}
}
