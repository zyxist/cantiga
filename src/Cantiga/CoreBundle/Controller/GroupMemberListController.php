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

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Controller\GroupPageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/group/{slug}/memberlist")
 * @Security("has_role('ROLE_GROUP_AWARE')")
 */
class GroupMemberListController extends GroupPageController
{
	use Traits\MemberListTrait;
	const REPOSITORY_NAME = 'cantiga.core.repo.group_memberlist';
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
			->setIndexPage('group_memberlist_index')
			->setInfoPage('group_memberlist_profile')
			->setInfoTemplate('profile.html.twig');
		$this->breadcrumbs()
			->workgroup('community')
			->entryLink($this->trans('Member list', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}
	
	protected function profilePageSubtitle()
	{
		return 'Group member profile';
	}
	
	/**
	 * @Route("/index", name="group_memberlist_index")
	 */
    public function indexAction(Request $request)
    {
		return $this->onIndex($request);
    }

	/**
	 * @Route("/{id}/profile", name="group_memberlist_profile")
	 */
	public function profileAction($id, Request $request)
	{
		return $this->onProfile($id, $request);
	}
}
