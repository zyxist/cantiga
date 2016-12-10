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

use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CoreBundle\Api\Modules;
use Cantiga\CoreBundle\Form\ProjectSettingsForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/settings")
 * @Security("is_granted('PLACE_MANAGER') and is_granted('MEMBEROF_PROJECT')")
 */
class ProjectSettingsController extends ProjectPageController
{	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->breadcrumbs()
			->workgroup('manage')
			->entryLink($this->trans('Settings', [], 'pages'), 'project_settings_index', ['slug' => $this->getSlug()]);
	}
	
	/**
	 * @Route("/index", name="project_settings_index")
	 */
	public function indexAction(Request $request)
	{
		$settings = $this->get('cantiga.project.settings');
		$form = $this->createForm(
			ProjectSettingsForm::class,
			$settings->toArray(),
			[
				'action' => $this->generateUrl('project_settings_index', ['slug' => $this->getSlug()]),
				'settings' => $settings,
				'extensionPoints' => $this->getExtensionPoints(),
				'filter' => $this->getExtensionPointFilter()
			]
		);
		
		$form->handleRequest($request);
		if ($form->isValid()) {
			$settings->fromArray($form->getData());
			$settings->saveSettings();
			$this->get('session')->getFlashBag()->add('info', $this->trans('The project settings have been saved.'));
		}
		
		return $this->render('CantigaCoreBundle:ProjectSettings:index.html.twig', array(
			'project' => $this->getActiveProject(),
			'projectSettings' => $settings->getOrganized(),
			'modules' => Modules::fetchAll(),
			'form' => $form->createView(),
		));
	}
}
