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
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Repository\AppTextRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;

/**
 * Displays a custom static text on a dashboard, if such a text is defined in the currently selected language.
 *
 * @author Tomasz Jędrzejewski
 */
class DashboardTextExtension implements DashboardExtensionInterface
{
	/**
	 * @var AppTextRepository 
	 */
	private $textRepository;
	/**
	 * @var EngineInterface
	 */
	private $templating;
	
	public function __construct(AppTextRepository $repository, EngineInterface $templating)
	{
		$this->textRepository = $repository;
		$this->templating = $templating;
	}
	
	public function getPriority()
	{
		return self::PRIORITY_HIGH + 5;
	}

	public function render(CantigaController $controller, Request $request, Workspace $workspace, Project $project = null)
	{
		$text = $this->textRepository->getTextOrFalse('cantiga:dashboard:'.$workspace->getKey(), $request, $project);
		if (false === $text) {
			return '';
		}
		return $this->templating->render('CantigaCoreBundle:AppText:dashboard-element.html.twig', ['text' => $text]);
	}
}
