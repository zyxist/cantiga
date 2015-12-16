<?php
namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CoreBundle\Entity\Invitation;
use Cantiga\CoreBundle\Form\InvitationForm;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/area/{id}/membership")
 * @Security("has_role('ROLE_PROJECT_MEMBER')")
 */
class ProjectAreaMembershipController extends ProjectPageController
{
	const INDEX_PAGE = 'project_area_membership_index';
	const REPOSITORY_NAME = 'cantiga.core.repo.project_area_membership';
	private $area;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$this->area = $repository->getItem($request->get('id'), $this->getActiveProject());

			$this->breadcrumbs()
				->workgroup('data')
				->entryLink($this->trans('Areas', [], 'pages'), 'project_area_index', ['slug' => $this->getSlug()])
				->link($this->area->getName(), 'project_area_info', ['slug' => $this->getSlug(), 'id' => $this->area->getId()])
				->link($this->trans('Area members', [], 'pages'), self::INDEX_PAGE, ['slug' => $this->getSlug(), 'id' => $this->area->getId()]);
		} catch(ItemNotFoundException $exception) {
			return $this->showPageWithError($this->trans('AreaNotFound'), 'project_area_index', ['slug' => $this->getSlug()]);
		}
	}
	
	/**
	 * @Route("/index", name="project_area_membership_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$roleResolver = $this->get('cantiga.roles');
		return $this->render('CantigaCoreBundle:ProjectAreaMembership:index.html.twig', array(
			'area' => $this->area,
			'roles' => $roleResolver->getRoles('Area')
		));
	}
	
	/**
	 * @Route("/ajax/reload", name="project_area_membership_ajax_reload")
	 */
	public function ajaxReloadAction(Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			return new JsonResponse($repository->findMembers($this->area));
		} catch (Exception $ex) {
			return new JsonResponse([]);
		}
	}
	
	/**
	 * @Route("/ajax/edit", name="project_area_membership_ajax_edit")
	 */
	public function ajaxEditAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		try {
			$member = $repository->getMember($this->area, $request->get('u'));
			$role = $repository->getRole($request->get('r'));
			$note = $request->get('n');
			return new JsonResponse($repository->editMember($this->area, $member, $role, $note));
		} catch(Exception $exception) {
			return ['status' => 0, 'error' => $exception->getMessage()];
		}
	}
	
	/**
	 * @Route("/ajax/remove", name="project_area_membership_ajax_remove")
	 */
	public function ajaxRemoveAction(Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$member = $repository->getMember($this->area, $request->get('u'));
			return new JsonResponse($repository->removeMember($this->area, $member));
		} catch(Exception $exception) {
			return ['status' => 0, 'error' => $exception->getMessage()];
		}
	}
	
	/**
	 * @Route("/invite", name="project_area_membership_invite")
	 */
	public function inviteAction(Request $request)
	{
		try {
			$roleResolver = $this->get('cantiga.roles');
			$repository = $this->get('cantiga.core.repo.invitation');
			$invitation = new Invitation();
			
			$form = $this->createForm(new InvitationForm($roleResolver->getRoles('Area')), $invitation, ['action' => $this->generateUrl('project_area_membership_invite', ['slug' => $this->getSlug(), 'id' => $this->area->getId()])]);
			$form->handleRequest($request);
			if ($form->isValid()) {
				$invitation->setInviter($this->getUser());
				$invitation->toEntity($this->area);
				
				$repository->invite($invitation);
				return $this->showPageWithMessage($this->trans('The invitation has been sent.'), self::INDEX_PAGE, ['slug' => $this->getSlug(), 'id' => $this->area->getId()]);
			}
			$this->breadcrumbs()->link($this->trans('Invite', [], 'general'), 'project_area_membership_invite', ['slug' => $this->getSlug(), 'id' => $this->area->getId()]);
			return $this->render('CantigaCoreBundle:ProjectAreaMembership:invite.html.twig', array(
				'area' => $this->area,
				'form' => $form->createView()
			));
		} catch (ModelException $ex) {
			return $this->showPageWithError($this->trans($ex->getMessage()), self::INDEX_PAGE, ['slug' => $this->getSlug(), 'id' => $this->area->getId()]);
		}
	}
}