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

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Controller\AreaPageController;
use Cantiga\CourseBundle\CourseTexts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/area/{slug}/course-results")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class AreaCourseResultController extends AreaPageController
{

	const REPOSITORY_NAME = 'cantiga.course.repo.area_summary';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$this->crudInfo = $this->newCrudInfo($repository)
			->setTemplateLocation('CantigaCourseBundle:Summary:')
			->setItemNameProperty('name')
			->setPageTitle('Course results')
			->setPageSubtitle('See the total course results of all the members of your area')
			->setIndexPage('area_course_index')
			->setInfoPage('area_course_info');

		$this->breadcrumbs()
			->workgroup('summary')
			->entryLink($this->trans('Course results', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="area_course_results")
	 */
	public function resultsAction(Request $request, Membership $membership)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$text = $this->getTextRepository()->getText(CourseTexts::AREA_COURSE_LIST_TEXT, $request);
		return $this->render($this->crudInfo->getTemplateLocation() . 'area-individual-results.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'courseInfoPage' => 'area_course_info',
			'userProfilePage' => 'memberlist_profile',
			'area' => $membership->getPlace(),
			'items' => $repository->findTotalIndividualResultsForArea($membership->getPlace()),
		));
	}

}
