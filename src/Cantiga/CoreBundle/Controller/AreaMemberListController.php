<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Controller\AreaPageController;

/**
 * @Route("/area/{slug}/memberlist")
 * @Security("has_role('ROLE_AREA_AWARE')")
 */
class AreaMemberListController extends AreaPageController
{

	use Traits\MemberListTrait;

	const REPOSITORY_NAME = 'cantiga.core.repo.area_memberlist';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->crudInfo = $this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('CantigaCoreBundle:MemberList:')
			->setItemNameProperty('name')
			->setPageTitle('Member list')
			->setPageSubtitle('Explore your colleagues')
			->setIndexPage('area_memberlist_index')
			->setInfoPage('area_memberlist_profile')
			->setInfoTemplate('profile.html.twig');
		$this->breadcrumbs()
			->workgroup('community')
			->entryLink($this->trans('Member list', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}

	protected function profilePageSubtitle()
	{
		return 'Area member profile';
	}

	/**
	 * @Route("/index", name="area_memberlist_index")
	 */
	public function indexAction(Request $request)
	{
		return $this->onIndex($request);
	}

	/**
	 * @Route("/{id}/profile", name="area_memberlist_profile")
	 */
	public function profileAction($id, Request $request)
	{
		return $this->onProfile($id, $request);
	}

}
