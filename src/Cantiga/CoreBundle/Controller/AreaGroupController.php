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

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Api\Controller\AreaPageController;
use Cantiga\UserBundle\Repository\MemberlistRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/area/{slug}/my-group")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class AreaGroupController extends AreaPageController
{
	/**
	 * @Route("/", name="area_my_group")
	 */
	public function myGroupAction(Request $request, Membership $membership)
	{
		$area = $membership->getPlace();
		$group = $area->getGroup();
		if (null === $area->getGroup()) {
			return $this->showPageWithError('AreaNotAssignedToGroupMsg', 'place_dashboard', ['slug' => $this->getSlug()]);
		}

		$this->breadcrumbs()
			->workgroup('community')
			->entryLink($this->trans('My group', [], 'pages'), 'area_my_group', ['slug' => $this->getSlug()]);
		return $this->render('CantigaCoreBundle:AreaGroup:my-group.html.twig', [
			'group' => $group,
			'members' => $this->getMemberlistRepository()->findMembers($group->getPlace()),
			'areas' => $group->findAreaSummary($this->get('database_connection'))
		]);
	}
	
	public function getMemberlistRepository(): MemberlistRepository
	{
		return $this->get('cantiga.user.repo.memberlist');
	}
}
