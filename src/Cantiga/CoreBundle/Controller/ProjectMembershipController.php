<?php
namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CoreBundle\Entity\Invitation;
use Cantiga\CoreBundle\Form\InvitationForm;
use Cantiga\Metamodel\Exception\ModelException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/membership")
 * @Security("has_role('ROLE_PROJECT_MANAGER')")
 */
class ProjectMembershipController extends ProjectPageController
{
	const REPOSITORY = 'cantiga.core.repo.admin_membership';
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->breadcrumbs()
			->workgroup('manage')
			->entryLink($this->trans('Project members', [], 'pages'), 'project_membership_index', ['slug' => $this->getSlug()]);
	}
	
	/**
	 * @Route("/index", name="project_membership_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY);
		$roleResolver = $this->get('cantiga.roles');
		return $this->render('CantigaCoreBundle:ProjectMembership:index.html.twig', array(
			'project' => $this->getActiveProject(),
			'roles' => $roleResolver->getRoles('Project')
		));
	}
	
	/**
	 * @Route("/ajax/hints", name="project_membership_ajax_hints")
	 */
	public function ajaxHintsAction(Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY);
			return new JsonResponse($repository->findHints($this->getActiveProject(), $request->get('q')));
		} catch (Exception $ex) {
			return new JsonResponse([]);
		}
	}
	
	/**
	 * @Route("/ajax/reload", name="project_membership_ajax_reload")
	 */
	public function ajaxReloadAction(Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY);
			return new JsonResponse($repository->findMembers($this->getActiveProject()));
		} catch (Exception $ex) {
			return new JsonResponse([]);
		}
	}
	
	/**
	 * @Route("/ajax/add", name="project_membership_ajax_add")
	 */
	public function ajaxAddAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY);
		try {
			$user = $repository->getUserByEmail($request->get('u'));
			$role = $repository->getRole($request->get('r'));
			$note = $request->get('n');

			return new JsonResponse($repository->joinMember($this->getActiveProject(), $user, $role, $note));
		} catch(Exception $exception) {
			return ['status' => 0, 'error' => $exception->getMessage()];
		}
	}
	
	/**
	 * @Route("/ajax/edit", name="project_membership_ajax_edit")
	 */
	public function ajaxEditAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY);
		try {
			$project = $this->getActiveProject();
			$member = $repository->getMember($project, $request->get('u'));
			$role = $repository->getRole($request->get('r'));
			$note = $request->get('n');
			return new JsonResponse($repository->editMember($project, $member, $role, $note));
		} catch(Exception $exception) {
			return ['status' => 0, 'error' => $exception->getMessage()];
		}
	}
	
	/**
	 * @Route("/ajax/remove", name="project_membership_ajax_remove")
	 */
	public function ajaxRemoveAction(Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY);
			$project = $this->getActiveProject();
			$member = $repository->getMember($project, $request->get('u'));
			return new JsonResponse($repository->removeMember($project, $member));
		} catch(Exception $exception) {
			return ['status' => 0, 'error' => $exception->getMessage()];
		}
	}
	
	/**
	 * @Route("/invite", name="project_membership_invite")
	 */
	public function inviteAction(Request $request)
	{
		try {
			$roleResolver = $this->get('cantiga.roles');
			$repository = $this->get('cantiga.core.repo.invitation');
			$invitation = new Invitation();
			
			$form = $this->createForm(new InvitationForm($roleResolver->getRoles('Project')), $invitation, ['action' => $this->generateUrl('project_membership_invite', ['slug' => $this->getSlug()])]);
			$form->handleRequest($request);
			if ($form->isValid()) {
				$invitation->setInviter($this->getUser());
				$invitation->toEntity($this->getActiveProject());
				
				$repository->invite($invitation);
				return $this->showPageWithMessage($this->trans('The invitation has been sent.'), 'project_membership_index', ['slug' => $this->getSlug()]);
			}
			$this->breadcrumbs()->link($this->trans('Invite', [], 'general'), 'project_membership_invite', ['slug' => $this->getSlug()]);
			return $this->render('CantigaCoreBundle:ProjectMembership:invite.html.twig', array(
				'project' => $this->getActiveProject(),
				'form' => $form->createView()
			));
		} catch (ModelException $ex) {
			return $this->showPageWithError($this->trans($ex->getMessage()), 'project_membership_index', ['slug' => $this->getSlug()]);
		}
	}
}
