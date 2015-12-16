<?php
namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\AreaPageController;
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
 * @Route("/area/{slug}/membership")
 * @Security("has_role('ROLE_AREA_MANAGER')")
 */
class AreaMembershipController extends AreaPageController
{
	const REPOSITORY = 'cantiga.core.repo.area_membership';
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->breadcrumbs()
			->workgroup('manage')
			->entryLink($this->trans('Area members', [], 'pages'), 'area_membership_index', ['slug' => $this->getSlug()]);
	}
	
	/**
	 * @Route("/index", name="area_membership_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY);
		$roleResolver = $this->get('cantiga.roles');
		return $this->render('CantigaCoreBundle:AreaMembership:index.html.twig', array(
			'area' => $this->getMembership()->getItem(),
			'roles' => $roleResolver->getRoles('Area')
		));
	}
	
	/**
	 * @Route("/ajax/reload", name="area_membership_ajax_reload")
	 */
	public function ajaxReloadAction(Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY);
			return new JsonResponse($repository->findMembers($this->getMembership()->getItem()));
		} catch (Exception $ex) {
			return new JsonResponse([]);
		}
	}
	
	/**
	 * @Route("/ajax/edit", name="area_membership_ajax_edit")
	 */
	public function ajaxEditAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY);
		try {
			$item = $this->getMembership()->getItem();
			$member = $repository->getMember($item, $request->get('u'));
			$role = $repository->getRole($request->get('r'));
			$note = $request->get('n');
			return new JsonResponse($repository->editMember($item, $member, $role, $note));
		} catch(Exception $exception) {
			return ['status' => 0, 'error' => $exception->getMessage()];
		}
	}
	
	/**
	 * @Route("/ajax/remove", name="area_membership_ajax_remove")
	 */
	public function ajaxRemoveAction(Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY);
			$item = $this->getMembership()->getItem();
			$member = $repository->getMember($item, $request->get('u'));
			return new JsonResponse($repository->removeMember($item, $member));
		} catch(Exception $exception) {
			return ['status' => 0, 'error' => $exception->getMessage()];
		}
	}
	
	/**
	 * @Route("/invite", name="area_membership_invite")
	 */
	public function inviteAction(Request $request)
	{
		try {
			$roleResolver = $this->get('cantiga.roles');
			$repository = $this->get('cantiga.core.repo.invitation');
			$invitation = new Invitation();
			
			$form = $this->createForm(new InvitationForm($roleResolver->getRoles('Area')), $invitation, ['action' => $this->generateUrl('area_membership_invite', ['slug' => $this->getSlug()])]);
			$form->handleRequest($request);
			if ($form->isValid()) {
				$invitation->setInviter($this->getUser());
				$invitation->toEntity($this->getMembership()->getItem());
				
				$repository->invite($invitation);
				return $this->showPageWithMessage($this->trans('The invitation has been sent.'), 'area_membership_index', ['slug' => $this->getSlug()]);
			}
			$this->breadcrumbs()->link($this->trans('Invite', [], 'general'), 'area_membership_invite', ['slug' => $this->getSlug()]);
			return $this->render('CantigaCoreBundle:AreaMembership:invite.html.twig', array(
				'area' => $this->getMembership()->getItem(),
				'form' => $form->createView()
			));
		} catch (ModelException $ex) {
			return $this->showPageWithError($this->trans($ex->getMessage()), 'area_membership_index', ['slug' => $this->getSlug()]);
		}
	}
}
