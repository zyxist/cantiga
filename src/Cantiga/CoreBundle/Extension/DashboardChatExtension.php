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

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\CoreSettings;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Repository\ProjectAreaRequestRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;

/**
 * Shows the most recent chat activity in the area requests.
 *
 * @author Tomasz Jędrzejewski
 */
class DashboardChatExtension implements DashboardExtensionInterface
{
	/**
	 * @var ProjectAreaRequestRepository
	 */
	private $repository;
	
	public function __construct(ProjectAreaRequestRepository $repository, EngineInterface $templating)
	{
		$this->repository = $repository;
		$this->templating = $templating;
	}
	
	public function getPriority()
	{
		return self::PRIORITY_MEDIUM - 10;
	}

	public function render(CantigaController $controller, Request $request, Workspace $workspace, Project $project = null)
	{
		if ($controller->getProjectSettings()->get(CoreSettings::DASHBOARD_SHOW_CHAT)->getValue()) {
			$this->repository->setActiveProject($project);
			return $this->templating->render('CantigaCoreBundle:Project:recent-comments.html.twig', ['comments' => $this->repository->getRecentFeedbackActivity(8)]);
		}
		return '';
	}
}
