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
namespace Cantiga\CoreBundle\Extension;

use Cantiga\Components\Hierarchy\MembershipStorageInterface;
use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Project;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;

/**
 * Displays a short information about the current area status.
 */
class AreaSummaryExtension implements DashboardExtensionInterface
{
	/**
	 * @var EngineInterface
	 */
	private $templating;
	/**
	 * @var MembershipStorageInterface
	 */
	private $membershipStorage;
	
	public function __construct(EngineInterface $templating, MembershipStorageInterface $membershipStorage)
	{
		$this->templating = $templating;
		$this->membershipStorage = $membershipStorage;
	}
	
	public function getPriority()
	{
		return self::PRIORITY_LOW;
	}

	public function render(CantigaController $controller, Request $request, Workspace $workspace, Project $project = null)
	{
		$area = $this->membershipStorage->getMembership()->getPlace();
		return $this->templating->render('CantigaCoreBundle:Area:area-summary.html.twig', ['area' => $area, 'bgcolor' => $this->translateColor($area)]);
	}
	
	private function translateColor(Area $area)
	{
		switch ($area->getStatus()->getLabel()) {
			case 'success':
				return 'green';
			case 'warning':
				return 'orange';
			case 'danger':
				return 'red';
			case 'primary':
				return 'blue';
			default:
				return 'grey';
		}
	}
}
