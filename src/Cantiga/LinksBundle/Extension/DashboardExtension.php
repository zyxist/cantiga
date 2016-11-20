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
namespace Cantiga\LinksBundle\Extension;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Extension\DashboardExtensionInterface;
use Cantiga\LinksBundle\Entity\Link;
use Cantiga\LinksBundle\Repository\LinkRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;

/**
 * Displays the links on the given dashboard.
 *
 * @author Tomasz Jędrzejewski
 */
class DashboardExtension implements DashboardExtensionInterface
{
	/**
	 * @var LinkRepository 
	 */
	private $linkRepository;
	/**
	 * @var EngineInterface
	 */
	private $templating;
	
	public function __construct(LinkRepository $repository, EngineInterface $templating)
	{
		$this->linkRepository = $repository;
		$this->templating = $templating;
	}
	
	public function getPriority()
	{
		return self::PRIORITY_HIGH;
	}

	public function render(CantigaController $controller, Request $request, Workspace $workspace, Project $project = null)
	{
		if (null !== $project) {
			$this->linkRepository->setProject($project);
		}
		$presentationPlace = null;
		if ($workspace instanceof Workspace\UserWorkspace) {
			$presentationPlace = Link::PRESENT_USER;
		} elseif ($workspace instanceof Workspace\AdminWorkspace) {
			$presentationPlace = Link::PRESENT_ADMIN;
		} elseif ($workspace instanceof Workspace\AreaWorkspace) {
			$presentationPlace = Link::PRESENT_AREA;
		} elseif ($workspace instanceof Workspace\GroupWorkspace) {
			$presentationPlace = Link::PRESENT_GROUP;
		} elseif ($workspace instanceof Workspace\ProjectWorkspace) {
			$presentationPlace = Link::PRESENT_PROJECT;
		}
		if (null === $presentationPlace) {
			return '';
		}
		return $this->templating->render('CantigaLinksBundle:Links:dashboard-element.html.twig', array('links' => $this->linkRepository->findLinks($presentationPlace)));
	}
}
