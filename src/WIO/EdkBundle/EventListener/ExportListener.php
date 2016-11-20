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

use Cantiga\ExportBundle\Event\ExportEvent;
use WIO\EdkBundle\Repository\EdkExportRepository;

/**
 * Handles the external data export.
 *
 * @author Tomasz Jędrzejewski
 */
class ExportListener
{
	/**
	 * @var EdkExportRepository
	 */
	private $repo;
	
	public function __construct(EdkExportRepository $repository)
	{
		$this->repo = $repository;
	}
	
	
	/**
	 * Handles the data export of additional area descriptions, routes and territories to the external systems.
	 * 
	 * @param \WIO\EdkBundle\EventListener\ExportEvent $event
	 */
	public function onProjectExported(ExportEvent $event)
	{
		$areaBlock = $event->getBlock('area');
		
		$territoryBlock = $this->repo->exportTerritories($event->getProjectId());
		list($routeBlock, $participantBlock) = $this->repo->exportRoutes($event->getLastExportAt(), $areaBlock->getIds(), $areaBlock->getUpdatedIds());
		$areaDescBlock = $this->repo->exportAreaDescriptions($event->getLastExportAt(), $areaBlock->getIds(), $areaBlock->getUpdatedIds());
		$routeDescBlock = $this->repo->exportRouteDescriptions($event->getLastExportAt(), $routeBlock->getIds(), $routeBlock->getUpdatedIds());
		
		$event->addBlock('territory', $territoryBlock);
		$event->addBlock('route', $routeBlock);
		$event->addBlock('areaDesc', $areaDescBlock);
		$event->addBlock('routeDesc', $routeDescBlock);
		$event->addBlock('participants', $participantBlock);
	}
}
