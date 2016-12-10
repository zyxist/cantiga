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

namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\ProjectAwareControllerInterface;
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CoreBundle\CoreExtensions;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/project/{slug}/buttons")
 * @Security("is_granted('PLACE_MEMBER') and is_granted('MEMBEROF_PROJECT')")
 */
class ProjectButtonController extends ProjectPageController
{
	/**
	 * @Route("/index", name="project_buttons")
	 */
	public function showButtonsAction()
	{
		$this->breadcrumbs()
			->workgroup('data')
			->entryLink($this->trans('Magic buttons', [], 'pages'), 'project_buttons', ['slug' => $this->getSlug()]);
		$extensions = $this->getExtensionPoints()->describeImplementations(CoreExtensions::MAGIC_BUTTON, $this->createExtensionFilter());
		return $this->render('CantigaCoreBundle:Project:buttons.html.twig', array('buttons' => $extensions));
	}
	
	/**
	 * @Route("/activate/{button}", name="project_buttons_activate")
	 */
	public function activateButton($button, Request $request)
	{
		try {
			$implementations = $this->getExtensionPoints()->findImplementations(CoreExtensions::MAGIC_BUTTON, $this->createExtensionFilter()
				->withServices([$button]));
			
			if (sizeof($implementations) != 1) {
				return $this->showPageWithError($this->trans('Invalid button implementation'), 'project_buttons', ['slug' => $this->getSlug()]);
			}
			return $implementations[0]->execute($this->getActiveProject());
		} catch (Exception $ex) {
			return $this->showPageWithError($this->trans($ex->getMessage()), 'project_buttons', ['slug' => $this->getSlug()]);
		}
	}
	
	private function createExtensionFilter()
	{
		$filter = $this->getExtensionPointFilter();
		if ($this instanceof ProjectAwareControllerInterface) {
			$modules = $this->getActiveProject()->getModules();
			$modules[] = 'core';
			$filter = $filter->withModules($modules);
		}
		return $filter;
	}
}
