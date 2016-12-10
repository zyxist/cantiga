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

namespace Cantiga\MilestoneBundle\Controller;

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Api\Controller\AreaPageController;
use Cantiga\MilestoneBundle\Controller\Traits\MilestoneEditorTrait;
use Cantiga\MilestoneBundle\MilestoneTexts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/area/{slug}/milestones")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class AreaMilestoneEditorController extends AreaPageController
{

	use MilestoneEditorTrait;

	const REPOSITORY_NAME = 'cantiga.milestone.repo.status';
	const MILESTONE_TEMPLATE = 'CantigaMilestoneBundle:MilestoneEditor:milestone-editor.html.twig';

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->repository = $this->get(self::REPOSITORY_NAME);
		$this->breadcrumbs()
			->workgroup('area')
			->entryLink($this->trans('Milestones', [], 'pages'), 'area_milestone_editor', ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/editor", name="area_milestone_editor")
	 */
	public function indexAction(Request $request, Membership $membership)
	{
		$text = $this->getTextRepository()->getText(MilestoneTexts::AREA_MILESTONE_EDITOR_TEXT, $request, $this->getActiveProject());
		return $this->render(self::MILESTONE_TEMPLATE, array(
			'pageTitle' => 'Milestones',
			'pageSubtitle' => 'View and manage the progress',
			'reloadPage' => 'area_milestone_ajax_reload',
			'completePage' => 'area_milestone_ajax_complete',
			'updatePage' => 'area_milestone_ajax_update',
			'cancelPage' => 'area_milestone_ajax_cancel',
			'selectorEnabled' => 0,
			'selectedEntity' => $membership->getPlace()->getPlace()->getId(),
			'text' => $text->getContent(),
		));
	}

	/**
	 * @Route("/ajax-reload", name="area_milestone_ajax_reload")
	 */
	public function ajaxReloadAction(Request $request, Membership $membership)
	{
		return $this->performReload($request, $membership);
	}

	/**
	 * @Route("/ajax-complete", name="area_milestone_ajax_complete")
	 */
	public function ajaxCompleteAction(Request $request, Membership $membership)
	{
		return $this->performComplete($request, $membership);
	}

	/**
	 * @Route("/ajax-cancel", name="area_milestone_ajax_cancel")
	 */
	public function ajaxCancelAction(Request $request, Membership $membership)
	{
		return $this->performCancel($request, $membership);
	}

	/**
	 * @Route("/ajax-update", name="area_milestone_ajax_update")
	 */
	public function ajaxUpdateAction(Request $request, Membership $membership)
	{
		return $this->performUpdate($request, $membership);
	}

}
