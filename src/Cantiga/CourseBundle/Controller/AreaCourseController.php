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
use Cantiga\CourseBundle\CourseSettings;
use Cantiga\CourseBundle\CourseTexts;
use Cantiga\CourseBundle\Entity\Question;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/area/{slug}/courses")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class AreaCourseController extends AreaPageController
{

	const REPOSITORY_NAME = 'cantiga.course.repo.area_course';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$membership = $this->get('cantiga.user.membership.storage')->getMembership();
		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setArea($membership->getPlace());
		$this->crudInfo = $this->newCrudInfo($repository)
			->setTemplateLocation('CantigaCourseBundle:AreaCourse:')
			->setItemNameProperty('name')
			->setPageTitle('Courses')
			->setPageSubtitle('Take the on-line courses')
			->setIndexPage('area_course_index')
			->setInfoPage('area_course_info');

		$this->breadcrumbs()
			->workgroup('area')
			->entryLink($this->trans('Courses', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="area_course_index")
	 */
	public function indexAction(Request $request, Membership $membership)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$text = $this->getTextRepository()->getText(CourseTexts::AREA_COURSE_LIST_TEXT, $request, $this->getActiveProject());
		return $this->render($this->crudInfo->getTemplateLocation() . 'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'courseListText' => $text,
			'infoPage' => $this->crudInfo->getInfoPage(),
			'items' => $repository->findAvailableCourses($this->getUser()),
			'area' => $membership->getPlace()
		));
	}

	/**
	 * @Route("/{id}/info", name="area_course_info")
	 */
	public function infoAction($id, Membership $membership)
	{
		try {
			$repo = $this->get(self::REPOSITORY_NAME);
			$item = $repo->getItem($id);
			$result = $repo->getTestResult($this->getUser(), $item);
			$areaResult = $repo->getAreaResult($membership->getPlace(), $item);
			$this->breadcrumbs()->link($item->getName(), 'area_course_info', ['slug' => $this->getSlug(), 'id' => $item->getId()]);
			return $this->render($this->crudInfo->getTemplateLocation() . 'info.html.twig', array(
					'item' => $item,
					'result' => $result,
					'areaResult' => $areaResult,
					'pageTitle' => $item->getName(),
					'pageSubtitle' => 'Take the on-line course',
			));
		} catch (ItemNotFoundException $exception) {
			return $this->showPageWithError($this->trans('CourseNotFoundMsg'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		}
	}

	/**
	 * @Route("/{id}/test", name="area_course_test")
	 */
	public function testAction($id, Request $request, Membership $membership)
	{
		try {
			$repo = $this->get(self::REPOSITORY_NAME);
			$item = $repo->getItem($id);
			$minQuestionNum = $this->getMinQuestionNum();
			if (!$item->hasTest()) {
				return $this->showPageWithError($this->trans('TestQuestionsNotPublishedMsg'), $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
			}
			$result = $repo->getTestResult($this->getUser(), $item);

			if ($result->getResult() == Question::RESULT_CORRECT) {
				return $this->showPageWithMessage($this->trans('CourseAlreadyCompletedMsg'), $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
			}

			if ($this->get('session')->has('trial')) {
				if (!$request->isMethod('POST')) {
					return $this->showPageWithError($this->trans('TestAlreadyInProgressMsg'), $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
				}
				$testTrial = $this->get('session')->get('trial');
			} else {
				$repo->startNewTrial($result);
				$testTrial = $item->getTest()->constructTestTrial($minQuestionNum);
				$this->get('session')->set('trial', $testTrial);
			}
			$form = $testTrial->generateTestForm($this->createFormBuilder(), $this->getTranslator());
			$form->handleRequest($request);

			if ($form->isValid()) {
				$this->get('session')->remove('trial');
				$ok = $testTrial->validateTestTrial($form->getData());
				$repo->completeTrial($result, $membership->getPlace(), $testTrial);
				if ($ok) {
					return $this->showPageWithMessage($this->trans('TestPassedMsg'), $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
				} else {
					return $this->showPageWithError($this->trans('TestFailedMsg'), $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
				}
			}

			$this->breadcrumbs()->link($this->trans('Test', [], 'course'), 'area_course_test', ['slug' => $this->getSlug(), 'id' => $item->getId()]);
			return $this->render($this->crudInfo->getTemplateLocation() . 'test.html.twig', array(
					'item' => $item,
					'form' => $form->createView(),
					'fieldNames' => $testTrial->generateFormFieldNames(),
					'testTime' => $testTrial->getTimeLimitInMinutes(),
					'pageSubtitle' => 'Take the on-line course',
			));
		} catch (ItemNotFoundException $exception) {
			return $this->showPageWithError($this->trans('CourseNotFoundMsg'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		} catch (TrainingTestException $exception) {
			return $this->showPageWithError($this->trans($exception->getMessage()), $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
		} catch (ModelException $exception) {
			return $this->showPageWithError($this->trans($exception->getMessage()), $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
		}
	}

	/**
	 * @Route("/{id}/complete", name="area_course_complete")
	 */
	public function goodFaithCompletionAction($id, Membership $membership)
	{
		try {
			$repo = $this->get(self::REPOSITORY_NAME);
			$item = $repo->getItem($id);

			if ($item->hasTest()) {
				return $this->showPageWithError($this->trans('CourseHasTestMsg'), $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
			}
			$repo->confirmGoodFaithCompletion($membership->getPlace(), $this->getUser(), $item);
			return $this->showPageWithMessage($this->trans('CourseCompletedConfirmationMsg'), $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
		} catch (ModelException $exception) {
			return $this->showPageWithError($this->trans($exception->getMessage()), $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
		}
	}

	private function getMinQuestionNum()
	{
		return (int) $this->getProjectSettings()->get(CourseSettings::MIN_QUESTION_NUM)->getValue();
	}

}
