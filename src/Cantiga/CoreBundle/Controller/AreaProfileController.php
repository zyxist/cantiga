<?php
namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Actions\FormAction;
use Cantiga\CoreBundle\Api\Controller\AreaPageController;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\CoreSettings;
use Cantiga\CoreBundle\Entity\Intent\AreaProfileIntent;
use Cantiga\CoreBundle\Form\AreaProfileForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/area/{slug}/profile")
 * @Security("has_role('ROLE_AREA_MEMBER')")
 */
class AreaProfileController extends AreaPageController
{
	const REPOSITORY = 'cantiga.core.repo.project_area';
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->breadcrumbs()->workgroup('area');
	}
	
	/**
	 * @Route("/editor", name="area_profile_editor")
	 */
	public function editorAction(Request $request)
	{
		$this->breadcrumbs()->entryLink($this->trans('Profile editor', [], 'pages'), 'area_profile_editor', ['slug' => $this->getSlug()]);
		$area = $this->getMembership()->getItem();
		$repo = $this->get(self::REPOSITORY);
		$territoryRepo = $this->get('cantiga.core.repo.project_territory');
		$territoryRepo->setProject($area->getProject());
		$formModel = $this->extensionPointFromSettings(CoreExtensions::AREA_FORM, CoreSettings::AREA_FORM);		
		
		$intent = new AreaProfileIntent($area, $repo);
		$action = new FormAction($intent, new AreaProfileForm($this->getProjectSettings(), $formModel, $territoryRepo));
		$action->slug($this->getSlug());
		return $action->action($this->generateUrl('area_profile_editor', ['slug' => $this->getSlug()]))
			->template('CantigaCoreBundle:AreaProfile:editor.html.twig')
			->redirect($this->generateUrl('area_profile_editor', ['slug' => $this->getSlug()]))
			->formSubmittedMessage('AreaProfileSaved')
			->customForm($formModel)
			->onSubmit(function(AreaProfileIntent $intent) use($repo) {
				$intent->execute();
			})
			->run($this, $request);
	}
}
