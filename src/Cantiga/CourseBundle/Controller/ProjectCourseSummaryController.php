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
namespace Cantiga\CourseBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/course-summary")
 * @Security("is_granted('PLACE_VISITOR')")
 */
class ProjectCourseSummaryController extends ProjectPageController
{
	const REPOSITORY_NAME = 'cantiga.course.repo.project_summary';
	const FILTER_NAME = 'cantiga.core.filter.area';
	
	use Traits\SummaryTrait;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->performInitialization('project_course_summary_index', 'project_course_summary_info', $request, $authChecker);
	}
	
	/**
	 * @Route("/index", name="project_course_summary_index")
	 */
	public function indexAction(Request $request)
	{
		$filter = $this->get(self::FILTER_NAME);
		$filter->setTargetProject($this->getActiveProject());
		return $this->performIndex('project_course_summary_ajax_list', $filter, $request);
	}
	
	/**
	 * @Route("/ajax-list", name="project_course_summary_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$filter = $this->get(self::FILTER_NAME);
		$filter->setTargetProject($this->getActiveProject());
		return $this->performAjaxList($filter, $request);
	}
	
	/**
	 * @Route("/{id}/info", name="project_course_summary_info")
	 */
	public function infoAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$area = $repository->getItem($id);
			return $this->performResults('project_course_summary_course', null, $area, $request);
		} catch(ItemNotFoundException $exception) {
			return $this->showPageWithError($this->trans($exception->getMessage()), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		}
	}
	
	/**
	 * @Route("/{id}/course", name="project_course_summary_course")
	 */
	public function showCourseAction($id, Request $request)
	{
		return $this->performCourse('project_course_summary_course', $id, $request);
	}
}
