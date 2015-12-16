<?php
namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\UserPageController;
use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointFilter;
use Cantiga\CoreBundle\CoreExtensions;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/user/places")
 * @Security("has_role('ROLE_USER')")
 */
class UserPlaceController extends UserPageController
{
	const REPOSITORY_NAME = 'cantiga.core.repo.invitation';

	/**
	 * @Route("/index", name="user_place_index")
	 */
    public function indexAction(Request $request)
    {
		$repository = $this->get(self::REPOSITORY_NAME);
		$this->breadcrumbs()
			->workgroup('areas')
			->entryLink($this->trans('Places'), 'user_place_index');
		
		$loaders = $this->getExtensionPoints()->findImplementations(CoreExtensions::MEMBERSHIP_LOADER, new ExtensionPointFilter());
		$user = $this->getUser();
		$places = [];
		foreach ($loaders as $loader) {
			foreach ($loader->loadProjectRepresentations($user) as $proj) {
				$places[] = $proj;
			}
		}
		
        return $this->render('CantigaCoreBundle:UserPlace:index.html.twig', array(
			'pageTitle' => 'Your places',
			'pageSubtitle' => 'Places you are a member of',
			'places' => $places,
		));
	}
}