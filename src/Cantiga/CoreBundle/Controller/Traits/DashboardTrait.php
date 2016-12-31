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
namespace Cantiga\CoreBundle\Controller\Traits;

use Cantiga\CoreBundle\Api\Controller\ProjectAwareControllerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Additional utilities for constructing dashboards.
 */
trait DashboardTrait
{
	/**
	 * Finds and processes all the dashboard extensions. Dashboard extensions must implement
	 * the interface <tt>DashboardExtensionInterface</tt>.
	 * 
	 * @param string $extensionPoint Name of the extension point
	 * @return array
	 */
	protected function findDashboardExtensions($extensionPoint)
	{
		$filter = $this->getExtensionPointFilter();
		if ($this instanceof ProjectAwareControllerInterface) {
			$modules = $this->getActiveProject()->getModules();
			$modules[] = 'core';
			$filter = $filter->withModules($modules);
		}
		
		$extensions = $this->getExtensionPoints()->findImplementations($extensionPoint, $filter);
		if (false === $extensions) {
			return [];
		}
		usort($extensions, function($a, $b) {
			return $a->getPriority() - $b->getPriority();
		});
		return $extensions;
	}
	
	protected function renderExtensions(Request $request, array $extensions)
	{
		$project = null;
		if ($this instanceof ProjectAwareControllerInterface) {
			$project = $this->getActiveProject();
		}
		if (sizeof($extensions) > 0) {
			$html = '';
			foreach ($extensions as $extension) {
				$html .= $extension->render($this, $request, $this->getWorkspace(), $project);
			}
			return $html;
		}
		return false;
	}
	
	protected function renderHelpPage(Request $request, $route, $pageName)
	{
		$textId = $this->findText($pageName);
		if (null === $textId) {
			throw $this->createNotFoundException('The specified help page does not exist.');
		}
		
		$text = $this->getTextRepository()->getText($textId, $request);
		$pages = $this->getWorkspace()->getHelpPages($this->get('router'));
		foreach ($pages as &$page) {
			if ($page['route'] == $pageName) {
				$page['current'] = true;
			} else {
				$page['current'] = false;
			}
		}
		$this->breadcrumbs()
			->staticItem($this->trans('Help', [], 'pages'))
			->url($text->getTitle(), $route, ['page' => $route]);
		return $this->render('CantigaCoreBundle:Components:help-page.html.twig', ['text' => $text, 'pages' => $pages, 'route' => $route]);
	}
	
	private function findText(string $route)
	{
		foreach ($this->getWorkspace()->getHelpPages() as $page) {
			if ($page['route'] == $route) {
				return $page['text'];
			}
		}
		return null;
	}
}
