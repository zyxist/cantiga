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
namespace Cantiga\CoreBundle\Controller\Traits;

use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Symfony\Component\HttpFoundation\Request;

/**
 * Utilities for making the member lists.
 *
 * @author Tomasz Jędrzejewski
 */
trait MemberListTrait
{
	protected function profilePageSubtitle()
	{
		return 'Member profile';
	}
	
	protected function onIndex(Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
        return $this->render($this->crudInfo->getTemplateLocation().'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'members' => $repository->findMembers($this->getMembership()->getItem()),
			'locale' => $request->getLocale(),
			'profilePage' => $this->crudInfo->getInfoPage(),
		));
	}
	
	protected function onProfile($id, Request $request)
	{
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug());
		$entity = $this->getMembership()->getItem();
		return $action
			->set('profilePageSubtitle', $this->profilePageSubtitle())
			->fetch(function($repository, $id) use($entity) {
				return $repository->getItem($entity, $id);
			})
			->run($this, $id);
	}
}
