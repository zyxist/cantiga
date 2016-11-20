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

namespace Cantiga\CourseBundle\Controller\Traits;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Filter\AreaFilter;
use Cantiga\CourseBundle\CourseTexts;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Tomasz Jędrzejewski
 */
trait SummaryTrait
{

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	protected function performInitialization($indexPage, $infoPage, Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setProject($this->getActiveProject());
		$this->crudInfo = $this->newCrudInfo($repository)
			->setTemplateLocation('CantigaCourseBundle:Summary:')
			->setItemNameProperty('name')
			->setIndexPage($indexPage)
			->setInfoPage($infoPage)
			->setPageTitle('Course results')
			->setPageSubtitle('View the course results');

		$this->breadcrumbs()
			->workgroup('summary')
			->entryLink($this->trans('Course results', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}

	public function performIndex($ajaxListPage, AreaFilter $filter, Request $request)
	{
		$filterForm = $filter->createForm($this->createFormBuilder($filter));
		$filterForm->handleRequest($request);

		$dataTable = $this->crudInfo->getRepository()->createDataTable();
		$dataTable->filter($filter);
		return $this->render($this->crudInfo->getTemplateLocation() . 'area-summary.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'ajaxListPage' => $ajaxListPage,
			'filterForm' => $filterForm->createView(),
			'filter' => $filter
		));
	}

	public function performAjaxList(AreaFilter $filter, Request $request)
	{
		$routes = $this->dataRoutes();
		$routes->link('info_link', $this->crudInfo->getInfoPage(), ['id' => '::id', 'slug' => $this->getSlug()]);

		$filterForm = $filter->createForm($this->createFormBuilder($filter));
		$filterForm->handleRequest($request);

		$repository = $this->crudInfo->getRepository();
		$dataTable = $repository->createDataTable();
		$dataTable->filter($filter);
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($dataTable)));
	}

	protected function performResults($courseInfoPage, $profilePage, Area $area, Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$text = $this->getTextRepository()->getText(CourseTexts::AREA_COURSE_LIST_TEXT, $request);
		$this->breadcrumbs()->link($area->getName(), $this->crudInfo->getInfoPage(), ['id' => $area->getId(), 'slug' => $this->getSlug()]);
		return $this->render($this->crudInfo->getTemplateLocation() . 'other-individual-results.html.twig', array(
				'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
				'courseInfoPage' => $courseInfoPage,
				'userProfilePage' => $profilePage,
				'area' => $area,
				'items' => $repository->findTotalIndividualResultsForArea($area),
				'indexPage' => $this->crudInfo->getIndexPage(),
		));
	}

	protected function performCourse($selfPage, $id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$course = $repository->getCourse($id);

			$this->breadcrumbs()
				->staticItem($this->trans('Courses', [], 'pages'))
				->link($course->getName(), $selfPage, ['id' => $course->getId(), 'slug' => $this->getSlug()]);
			return $this->render($this->crudInfo->getTemplateLocation() . 'course-info.html.twig', array(
					'pageTitle' => $this->crudInfo->getPageTitle(),
					'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
					'item' => $course,
					'indexPage' => $this->crudInfo->getIndexPage(),
			));
		} catch (ItemNotFoundException $exception) {
			return $this->showPageWithError($this->trans($exception->getMessage()), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		}
	}

}
