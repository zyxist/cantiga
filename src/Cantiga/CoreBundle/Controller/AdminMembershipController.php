<?php
namespace Cantiga\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Cantiga\CoreBundle\Api\Controller\AdminPageController;
use Cantiga\CoreBundle\Entity\Project;

/**
 * @Route("/admin/membership")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminMembershipController extends AdminPageController
{
	const REPOSITORY = 'cantiga.core.repo.admin_membership';
	
	/**
	 * @Route("/index", name="admin_membership_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY);
		$roleResolver = $this->get('cantiga.roles');
		return $this->render('CantigaCoreBundle:AdminMembership:index.html.twig', array(
			'projects' => $repository->findActiveProjects(),
			'roles' => $roleResolver->getRoles('Project')
		));
	}
	
	/**
	 * @Route("/ajax/hints", name="admin_membership_ajax_hints")
	 */
	public function ajaxHintsAction(Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY);
			$project = $repository->getProject($request->get('p'));
			return new JsonResponse($repository->findHints($project, $request->get('q')));
		} catch (Exception $ex) {
			return new JsonResponse([]);
		}
	}
	
	/**
	 * @Route("/ajax/reload", name="admin_membership_ajax_reload")
	 */
	public function ajaxReloadAction(Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY);
			$project = $repository->getProject($request->get('p'));
			return new JsonResponse($repository->findMembers($project));
		} catch (Exception $ex) {
			return new JsonResponse([]);
		}
	}
	
	/**
	 * @Route("/ajax/add", name="admin_membership_ajax_add")
	 */
	public function ajaxAddAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY);
		try {
			$project = $repository->getProject($request->get('p'));
			$user = $repository->getUserByEmail($request->get('u'));
			$role = $repository->getRole($request->get('r'));
			$note = $request->get('n');

			return new JsonResponse($repository->joinMember($project, $user, $role, $note));
		} catch(Exception $exception) {
			return ['status' => 0, 'error' => $exception->getMessage()];
		}
	}
	
	/**
	 * @Route("/ajax/edit", name="admin_membership_ajax_edit")
	 */
	public function ajaxEditAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY);
		try {
			$project = $repository->getProject($request->get('p'));
			$member = $repository->getMember($project, $request->get('u'));
			$role = $repository->getRole($request->get('r'));
			$note = $request->get('n');

			return new JsonResponse($repository->editMember($project, $member, $role, $note));
		} catch(Exception $exception) {
			return ['status' => 0, 'error' => $exception->getMessage()];
		}
	}
	
	/**
	 * @Route("/ajax/remove", name="admin_membership_ajax_remove")
	 */
	public function ajaxRemoveAction(Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY);
			$project = $repository->getProject($request->get('p'));
			$member = $repository->getMember($project, $request->get('u'));
			return new JsonResponse($repository->removeMember($project, $member));
		} catch(Exception $exception) {
			return ['status' => 0, 'error' => $exception->getMessage()];
		}
	}
}
