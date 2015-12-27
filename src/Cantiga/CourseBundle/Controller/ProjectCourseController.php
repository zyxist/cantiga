<?php
namespace Cantiga\CourseBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CourseBundle\Form\CourseForm;
use Cantiga\CourseBundle\Entity\Course;

/**
 * @Route("/project/{slug}/courses")
 * @Security("has_role('ROLE_PROJECT_MANAGER')")
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
        return $this->render($this->crudInfo->getTemplateLocation().'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'insertPage' => $this->crudInfo->getInsertPage(),
			'ajaxListPage' => 'project_course_ajax_list'
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
		
		$action = new InsertAction($this->crudInfo, $entity, new CourseForm());
		$action->slug($this->getSlug());
		return $action->run($this, $request);
	}
	
	/**
	 * @Route("/{id}/edit", name="project_course_edit")
	 */
	public function editAction($id, Request $request)
	{
		$action = new EditAction($this->crudInfo, new CourseForm());
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
}