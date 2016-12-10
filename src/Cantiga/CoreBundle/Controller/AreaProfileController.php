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
namespace Cantiga\CoreBundle\Controller;

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Api\Actions\FormAction;
use Cantiga\CoreBundle\Api\Controller\AreaPageController;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\CoreSettings;
use Cantiga\CoreBundle\CoreTexts;
use Cantiga\CoreBundle\Entity\Intent\AreaProfileIntent;
use Cantiga\CoreBundle\Form\AreaProfileForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/area/{slug}/profile")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class AreaProfileController extends AreaPageController
{
	const REPOSITORY = 'cantiga.core.repo.area_mgmt';
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->breadcrumbs()->workgroup('area');
	}
	
	/**
	 * @Route("/editor", name="area_profile_editor")
	 */
	public function editorAction(Request $request, Membership $membership)
	{
		$this->breadcrumbs()->entryLink($this->trans('Profile editor', [], 'pages'), 'area_profile_editor', ['slug' => $this->getSlug()]);
		$area = $membership->getPlace();
		$repo = $this->get(self::REPOSITORY);
		$territoryRepo = $this->get('cantiga.core.repo.project_territory');
		$territoryRepo->setProject($area->getProject());
		$formModel = $this->extensionPointFromSettings(CoreExtensions::AREA_FORM, CoreSettings::AREA_FORM);		
		
		$text = $this->getTextRepository()->getTextOrFalse(CoreTexts::AREA_PROFILE_EDITOR_TEXT, $request, $this->getActiveProject());
		
		$intent = new AreaProfileIntent($area, $repo);
		$action = new FormAction($intent, AreaProfileForm::class, [
			'projectSettings' => $this->getProjectSettings(),
			'customFormModel' => $formModel,
			'territoryRepository' => $territoryRepo]);
		$action->slug($this->getSlug());
		return $action->action($this->generateUrl('area_profile_editor', ['slug' => $this->getSlug()]))
			->template('CantigaCoreBundle:AreaProfile:editor.html.twig')
			->redirect($this->generateUrl('area_profile_editor', ['slug' => $this->getSlug()]))
			->formSubmittedMessage('AreaProfileSaved')
			->set('progressBarColor', $area->getPercentCompleteness() < 50 ? 'red' : ($area->getPercentCompleteness() < 80 ? 'orange' : 'green'))
			->set('percentCompleteness', $area->getPercentCompleteness())
			->set('text', $text)
			->customForm($formModel)
			->onSubmit(function(AreaProfileIntent $intent) use($repo) {
				$intent->execute();
			})
			->run($this, $request);
	}
}
