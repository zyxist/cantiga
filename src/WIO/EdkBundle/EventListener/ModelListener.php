<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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
use Cantiga\ExportBundle\Event\ExportEvent;
use Cantiga\MilestoneBundle\Entity\NewMilestoneStatus;
use Cantiga\MilestoneBundle\Event\ActivationEvent;
use Cantiga\MilestoneBundle\MilestoneEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
	
	public function __construct(EventDispatcherInterface $eventDispatcher, EdkExportRepository $exportRepository)
	{
		$this->eventDispatcher = $eventDispatcher;
	}

	public function onAreaUpdated(AreaEvent $event)
	{
		$area = $event->getArea();
		$this->eventDispatcher->dispatch(MilestoneEvents::ACTIVATION_EVENT, new ActivationEvent($area->getProject(), $area->getEntity(), 'profile.updated', function() use($area) {
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
