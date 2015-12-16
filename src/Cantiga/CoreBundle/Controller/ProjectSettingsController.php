<?php
namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CoreBundle\Api\Modules;
use Cantiga\CoreBundle\Form\ProjectSettingsForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/settings")
 * @Security("has_role('ROLE_PROJECT_MANAGER')")
 */
class ProjectSettingsController extends ProjectPageController
{	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->breadcrumbs()
			->workgroup('manage')
			->entryLink($this->trans('Settings', [], 'pages'), 'project_settings_index', ['slug' => $this->getSlug()]);
	}
	
	/**
	 * @Route("/index", name="project_settings_index")
	 */
	public function indexAction(Request $request)
	{
		$settings = $this->get('cantiga.project.settings');
		$form = $this->createForm(
			new ProjectSettingsForm($settings, $this->getExtensionPoints(), $this->getExtensionPointFilter()),
			$settings->toArray(),
			array('action' => $this->generateUrl('project_settings_index', ['slug' => $this->getSlug()]))
		);
		
		$form->handleRequest($request);
		if ($form->isValid()) {
			$settings->fromArray($form->getData());
			$settings->saveSettings();
			$this->get('session')->getFlashBag()->add('info', $this->trans('The project settings have been saved.'));
		}
		
		return $this->render('CantigaCoreBundle:ProjectSettings:index.html.twig', array(
			'project' => $this->getActiveProject(),
			'projectSettings' => $settings->getOrganized(),
			'modules' => Modules::fetchAll(),
			'form' => $form->createView(),
		));
	}
}
