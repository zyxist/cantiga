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
namespace Cantiga\UserBundle\Controller;

use Cantiga\Components\Hierarchy\Entity\ExternalMember;
use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Controller\ProjectAwareControllerInterface;
use Cantiga\CoreBundle\Api\Controller\WorkspaceController;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\UserBundle\UserExtensions;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/s/{slug}/members")
 * @Security("is_granted('MEMBEROF_ANY')")
 */
class MemberlistController extends WorkspaceController
{
	const REPOSITORY_NAME = 'cantiga.user.repo.memberlist';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->crudInfo = $this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('CantigaUserBundle:Memberlist:')
			->setItemNameProperty('name')
			->setPageTitle('Member list')
			->setPageSubtitle('Explore your colleagues')
			->setIndexPage('memberlist_index')
			->setInfoPage('memberlist_profile')
			->setInfoTemplate('profile.html.twig');
		$this->breadcrumbs()
			->workgroup('community')
			->entryLink($this->trans('Member list', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}
	
	/**
	 * @Route("/index", name="memberlist_index")
	 */
	public function indexAction(Request $request, Membership $membership)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		return $this->render($this->crudInfo->getTemplateLocation() . 'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'members' => $repository->findMembers($membership->getPlace()->getPlace()),
			'locale' => $request->getLocale(),
			'profilePage' => $this->crudInfo->getInfoPage(),
		));
	}

	/**
	 * @Route("/{id}/profile", name="memberlist_profile")
	 */
	public function profileAction($id, Request $request, Membership $membership)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$member = $repository->getItem($this->getActiveProject(), $membership->getPlace()->getPlace(), $id);
			$this->breadcrumbs()
				->link($member->getName(), $this->crudInfo->getInfoPage(), ['slug' => $this->getSlug(), 'id' => $id]);
			
			if ($member instanceof ExternalMember) {
				return $this->render($this->crudInfo->getTemplateLocation() . 'external-profile.html.twig', array(
					'member' => $member,
					'locale' => $request->getLocale(),
				));
			} else {
				return $this->render($this->crudInfo->getTemplateLocation() . 'profile.html.twig', array(
					'member' => $member,
					'extensions' => $this->findProfileExtensions(UserExtensions::PROFILE_EXTENSION),
					'locale' => $request->getLocale(),
				));
			}
		} catch (ItemNotFoundException $ex) {
			return $this->showMessage($this->crudInfo->getPageTitle(), $this->trans('NoSuchMemberText', [], 'users'));
		} catch (ModelException $exception) {
			return $this->showPageWithError($this->trans($exception->getMessage(), [], 'users'), $this->crudInfo->getIndexPage(), []);
		}
	}
	
	private function findProfileExtensions($extensionPoint)
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
		return $extensions;
	}
}
