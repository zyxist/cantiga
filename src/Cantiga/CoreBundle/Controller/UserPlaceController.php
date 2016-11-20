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

use Cantiga\CoreBundle\Api\Controller\UserPageController;
use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointFilter;
use Cantiga\CoreBundle\CoreExtensions;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/user/places")
 * @Security("has_role('ROLE_USER')")
 */
class UserPlaceController extends UserPageController
{

	const REPOSITORY_NAME = 'cantiga.core.repo.invitation';

	/**
	 * @Route("/index", name="user_place_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$this->breadcrumbs()
			->workgroup('areas')
			->entryLink($this->trans('Places'), 'user_place_index');

		$loaders = $this->getExtensionPoints()->findImplementations(CoreExtensions::MEMBERSHIP_LOADER, new ExtensionPointFilter());
		$user = $this->getUser();
		$places = [];
		foreach ($loaders as $loader) {
			foreach ($loader->loadProjectRepresentations($user) as $proj) {
				$places[] = $proj;
			}
		}

		return $this->render('CantigaCoreBundle:UserPlace:index.html.twig', array(
				'pageTitle' => 'Your places',
				'pageSubtitle' => 'Places you are a member of',
				'places' => $places,
		));
	}

}
