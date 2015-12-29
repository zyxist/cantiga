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
namespace Cantiga\CourseBundle\Controller;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Controller\AreaPageController;
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
 * @Security("has_role('ROLE_AREA_AWARE')")
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
		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setArea($this->getMembership()->getItem());
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
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$text = $this->getTextRepository()->getText(CourseTexts::AREA_COURSE_LIST_TEXT, $request);
        return $this->render($this->crudInfo->getTemplateLocation().'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'courseListText' => $text,
			'infoPage' => $this->crudInfo->getInfoPage(),
			'items' => $repository->findAvailableCourses()
		));
	}
	
	/**
	 * @Route("/{id}/info", name="area_course_info")
	 */
	public function infoAction($id)
	{
		try {
			$repo = $this->get(self::REPOSITORY_NAME);
			$item = $repo->getItem($id);
			$result = $repo->getTestResult($this->getMembership()->getItem(), $item);
			$this->breadcrumbs()->link($item->getName(), 'area_course_info', ['slug' => $this->getSlug(), 'id' => $item->getId()]);
			return $this->render($this->crudInfo->getTemplateLocation().'info.html.twig', array(
				'item' => $item,
				'result' => $result,
				'pageTitle' => $item->getName(),
				'pageSubtitle' => 'Take the on-line course',
			));
		} catch(ItemNotFoundException $exception) {
			return $this->showPageWithError('The specified course has not been found.', $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		}
	}
	
	/**
	 * @Route("/{id}/test", name="area_course_test")
	 */
	public function testAction($id, Request $request) {
		try {
			$repo = $this->get(self::REPOSITORY_NAME);
			$item = $repo->getItem($id);
			
			if(!$item->hasTest()) {
				return $this->showPageWithError('The test questions have not been published for this course yet.', $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
			}
			$result = $repo->getTestResult($this->getUser(), $item);
			
			if($result->getResult() == Question::RESULT_CORRECT) {
				return $this->showPageWithMessage('You have already completed this training with a positive result.', $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
			}
			
			if($this->get('session')->has('trial')) {
				if(!$request->isMethod('POST')) {
					return $this->showPageWithError('This test is already in progress.', $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
				}
				$testTrial = $this->get('session')->get('trial');
			} else {
				$result->startNewTrial();
				$testTrial = $item->getTest()->constructTestTrial();
				$this->get('session')->set('trial', $testTrial);
			}
			$form = $testTrial->generateTestForm($this->createFormBuilder());
			$form->handleRequest($request);
			
			if($form->isValid()) {
				$this->get('session')->remove('trial');
				$ok = $testTrial->validateTestTrial($form->getData());
				$result->completeTrial($testTrial);
				if($ok) {
					return $this->showPageWithMessage('You have passed the test!', $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
				} else {
					return $this->showPageWithError('You have failed the test!', $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
				}
			}
			
			return $this->render($this->crudInfo->getTemplateLocation().'test.html.twig', array(
				'item' => $item,
				'form' => $form->createView(),
				'testTime' => $testTrial->getTimeLimitInMinutes()
			));
		} catch(ItemNotFoundException $exception) {
			return $this->showPageWithError('The given course has not been found.', $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		} catch(TrainingTestException $exception) {
			return $this->showPageWithError($this->get('translator')->trans($exception->getMessage()), $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
		} catch(ModelException $exception) {
			return $this->showPageWithError('The test questions have not been published for this course yet.', $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
		}
	}
	
	/**
	 * @Route("/{id}/complete", name="area_course_complete")
	 */
	public function goodFaithCompletionAction($id) {
		try {
			$repo = $this->get(self::REPOSITORY_NAME);
			$item = $repo->getItem($id);
			
			if($item->hasTest()) {
				return $this->showPageWithError('This course has a test - please solve it.', $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
			}
			$item->confirmGoodFaithCompletion($this->getUser());
			return $this->showPageWithMessage('Thank you for confirming the course completion.', $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
		} catch(ModelException $exception) {
			return $this->showPageWithError('An error has occurred during saving the course results.', $this->crudInfo->getInfoPage(), ['id' => $id, 'slug' => $this->getSlug()]);
		}
	}
}
