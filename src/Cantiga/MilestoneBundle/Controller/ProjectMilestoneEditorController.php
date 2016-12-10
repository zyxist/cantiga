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
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\MilestoneBundle\Controller\Traits\MilestoneEditorTrait;
use Cantiga\MilestoneBundle\MilestoneTexts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/milestones")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class ProjectMilestoneEditorController extends ProjectPageController
{

	use MilestoneEditorTrait;

	const REPOSITORY_NAME = 'cantiga.milestone.repo.status';
	const MILESTONE_TEMPLATE = 'CantigaMilestoneBundle:MilestoneEditor:milestone-editor.html.twig';

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->repository = $this->get(self::REPOSITORY_NAME);
		$this->breadcrumbs()
			->workgroup('data')
			->entryLink($this->trans('Milestones', [], 'pages'), 'project_milestone_editor', ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/editor/{i}", name="project_milestone_editor", defaults={"i" = "current"})
	 */
	public function indexAction($i, Request $request, Membership $membership)
	{
		if ($i === 'current' || !ctype_digit($i)) {
			$i = $membership->getPlace()->getPlace()->getId();
		}

		$text = $this->getTextRepository()->getText(MilestoneTexts::PROJECT_MILESTONE_EDITOR_TEXT, $request, $this->getActiveProject());
		return $this->render(self::MILESTONE_TEMPLATE, array(
			'pageTitle' => 'Milestones',
			'pageSubtitle' => 'View and manage the progress',
			'reloadPage' => 'project_milestone_ajax_reload',
			'completePage' => 'project_milestone_ajax_complete',
			'updatePage' => 'project_milestone_ajax_update',
			'cancelPage' => 'project_milestone_ajax_cancel',
			'selectorEnabled' => true,
			'selectedEntity' => $i,
			'text' => $text->getContent(),
		));
	}

	/**
	 * @Route("/ajax-reload", name="project_milestone_ajax_reload")
	 */
	public function ajaxReloadAction(Request $request, Membership $membership)
	{
		return $this->performReload($request, $membership);
	}

	/**
	 * @Route("/ajax-complete", name="project_milestone_ajax_complete")
	 */
	public function ajaxCompleteAction(Request $request, Membership $membership)
	{
		return $this->performComplete($request, $membership);
	}

	/**
	 * @Route("/ajax-cancel", name="project_milestone_ajax_cancel")
	 */
	public function ajaxCancelAction(Request $request, Membership $membership)
	{
		return $this->performCancel($request, $membership);
	}

	/**
	 * @Route("/ajax-update", name="project_milestone_ajax_update")
	 */
	public function ajaxUpdateAction(Request $request, Membership $membership)
	{
		return $this->performUpdate($request, $membership);
	}

}
