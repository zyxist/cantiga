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
use Cantiga\Components\Hierarchy\Importer\ImporterInterface;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CourseBundle\CourseSettings;
use Cantiga\CourseBundle\Entity\Course;
use Cantiga\CourseBundle\Entity\CourseTest;
use Cantiga\CourseBundle\Form\CourseForm;
use Cantiga\CourseBundle\Form\CourseTestUploadForm;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/courses")
 * @Security("is_granted('PLACE_MANAGER')")
 */
class ProjectCourseController extends ProjectPageController
{

	const REPOSITORY_NAME = 'cantiga.course.repo.course';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setProject($this->getActiveProject());
		$this->crudInfo = $this->newCrudInfo($repository)
			->setTemplateLocation('CantigaCourseBundle:ProjectCourse:')
			->setItemNameProperty('name')
			->setPageTitle('Courses')
			->setPageSubtitle('Manage the on-line courses for areas')
			->setIndexPage('project_course_index')
			->setInfoPage('project_course_info')
			->setInsertPage('project_course_insert')
			->setEditPage('project_course_edit')
			->setRemovePage('project_course_remove')
			->setItemCreatedMessage('The course \'0\' has been created.')
			->setItemUpdatedMessage('The course \'0\' has been updated.')
			->setItemRemovedMessage('The course \'0\' has been removed.')
			->setRemoveQuestion('CourseRemoveQuestion');

		$this->breadcrumbs()
			->workgroup('manage')
			->entryLink($this->trans('Courses', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="project_course_index")
	 */
	public function indexAction(Request $request)
	{
		$dataTable = $this->crudInfo->getRepository()->createDataTable();
		return $this->render($this->crudInfo->getTemplateLocation() . 'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'insertPage' => $this->crudInfo->getInsertPage(),
			'ajaxListPage' => 'project_course_ajax_list',
			'importer' => $this->getImportService(),
		));
	}

	/**
	 * @Route("/ajax-list", name="project_course_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', $this->crudInfo->getInfoPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('edit_link', $this->crudInfo->getEditPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('remove_link', $this->crudInfo->getRemovePage(), ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->crudInfo->getRepository();
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($dataTable, $this->get('cantiga.time'))));
	}

	/**
	 * @Route("/{id}/info", name="project_course_info")
	 */
	public function infoAction($id)
	{
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id);
	}

	/**
	 * @Route("/insert", name="project_course_insert")
	 */
	public function insertAction(Request $request)
	{
		$entity = new Course();
		$entity->setProject($this->getActiveProject());

		$action = new InsertAction($this->crudInfo, $entity, CourseForm::class);
		$action->slug($this->getSlug());
		return $action->run($this, $request);
	}

	/**
	 * @Route("/{id}/edit", name="project_course_edit")
	 */
	public function editAction($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, CourseForm::class);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}

	/**
	 * @Route("/{id}/remove", name="project_course_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}
	
	/**
	 * @Route("/import", name="project_course_import")
	 */
	public function importAction(Request $request, Membership $membership)
	{
		try {
			$importer = $this->getImportService();
			$repository = $this->get(self::REPOSITORY_NAME);
			if (!$importer->isImportAvailable()) {
				return $this->showPageWithError($this->trans('ImportNotPossibleText'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
			}
			$question = $importer->getImportQuestion($this->crudInfo->getPageTitle(), 'ImportCourseQuestionText');
			$question->path($this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
			$question->respond('project_course_import', ['slug' => $this->getSlug()]);
			$question->onSuccess(function() use ($repository, $importer) {
				$repository->importFrom($importer->getImportSource(), $importer->getImportDestination());
			});
			$this->breadcrumbs()->link($this->trans('Import', [], 'general'), 'project_course_import', ['slug' => $this->getSlug()]);
			return $question->handleRequest($this, $request);
		} catch(ModelException $exception) {
			return $this->showPageWithError($exception->getMessage(), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		}
	}

	/**
	 * @Route("/{id}/upload-test", name="project_course_upload_test")
	 */
	public function uploadTestAction($id, Request $request)
	{
		try {
			$repository = $this->crudInfo->getRepository();
			$item = $repository->getItem($id);
			$form = $this->createForm(CourseTestUploadForm::class);
			$form->handleRequest($request);

			if ($form->isValid()) {
				$data = $form->getData();
				if (!$data['file'] instanceof UploadedFile) {
					return $this->showPageWithError($this->trans('An error occurred during uploading the test questions.'), $this->crudInfo->getInfoPage(), array('id' => $id, 'slug' => $this->getSlug()));
				}
				if ($data['file']->getMimeType() != 'application/xml') {
					return $this->showPageWithError($this->trans('Please upload an XML file!'), $this->crudInfo->getInfoPage(), array('id' => $id, 'slug' => $this->getSlug()));
				}
				$content = file_get_contents($data['file']->getRealPath());
				$item->createTest($content);
				$this->verifyFileCorrectness($item->getTest());
				$repository->saveTest($item);
				return $this->showPageWithMessage($this->trans('The course test questions have been uploaded correctly.'), $this->crudInfo->getInfoPage(), array('id' => $id, 'slug' => $this->getSlug()));
			}
			$this->breadcrumbs()
				->link($item->getName(), 'project_course_info', ['id' => $item->getId(), 'slug' => $this->getSlug()])
				->link($this->trans('Upload test'), 'project_course_upload_test', ['id' => $item->getId(), 'slug' => $this->getSlug()]);
			return $this->render($this->crudInfo->getTemplateLocation() . 'upload-test.html.twig', array(
					'pageTitle' => $this->crudInfo->getPageTitle(),
					'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
					'item' => $item,
					'form' => $form->createView(),
			));
		} catch (ItemNotFoundException $exception) {
			return $this->showPageWithError($this->crudInfo->getItemNotFoundErrorMessage(), $this->crudInfo->getIndexPage());
		} catch (ModelException $exception) {
			return $this->showPageWithError($this->trans($exception->getMessage()), $this->crudInfo->getIndexPage());
		}
	}

	private function verifyFileCorrectness(CourseTest $test)
	{
		return $test->constructTestTrial($this->getMinQuestionNum());
	}

	private function getMinQuestionNum()
	{
		return (int) $this->getProjectSettings()->get(CourseSettings::MIN_QUESTION_NUM)->getValue();
	}
	
	public function getImportService(): ImporterInterface
	{
		return $this->get('cantiga.importer');
	}

}
