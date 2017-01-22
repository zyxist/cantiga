<?php
/*
 * This file is part of Cantiga Project. Copyright 2016-2017 Cantiga contributors.
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
 * along with Cantiga; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace Cantiga\AppTextBundle\Extension;

use Cantiga\Components\Application\AppTextHolderInterface;
use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Extension\DashboardExtensionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;

/**
 * Displays a custom static text on a dashboard, if such a text is defined in the currently selected language.
 */
class DashboardTextExtension implements DashboardExtensionInterface
{
	/**
	 * @var AppTextHolderInterface
	 */
	private $textHolder;
	/**
	 * @var EngineInterface
	 */
	private $templating;

	public function __construct(AppTextHolderInterface $holder, EngineInterface $templating)
	{
		$this->textHolder = $holder;
		$this->templating = $templating;
	}

	public function getPriority()
	{
		return self::PRIORITY_HIGH + 5;
	}

	public function render(CantigaController $controller, Request $request, Workspace $workspace, Project $project = null)
	{
		$text = $this->textHolder->findText('cantiga:dashboard:'.$workspace->getKey(), $project);
		if (!$text->isPresent()) {
			return '';
		}
		return $this->templating->render('CantigaAppTextBundle:AppText:dashboard-element.html.twig', ['text' => $text]);
	}
}
