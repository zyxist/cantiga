<?php
namespace Cantiga\CoreBundle\Api\Controller;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Breadcrumbs;
use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointFilter;
use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointsInterface;
use Cantiga\CoreBundle\Repository\AppTextRepository;
use Cantiga\CoreBundle\Repository\ProjectFormRepository;
use Cantiga\CoreBundle\Settings\ProjectSettings;
use Cantiga\Metamodel\DataRoutes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Extends the default Symfony controller with additional useful methods and shortcuts for
 * the most common operations.
 *
 * @author Tomasz Jędrzejewski
 */
class CantigaController extends Controller
{
	private $translator;
	private $breadcrumbs;
	private $locale;
	
	/**
	 * This method will be spawned before any main action in this controller. Can be used for
	 * setting up something. Cantiga takes care of running it.
	 * 
	 * @param Request $request
	 * @param AuthorizationCheckerInterface $authChecker
	 */
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
	}
	
	/**
	 * Without it, the translation won't work as expected.
	 * 
	 * @param Request $request
	 */
	public function setLocale(Request $request)
	{
		$this->locale = $request->getLocale();
	}
	
	/**
	 * @return Breadcrumbs
	 */
	public function breadcrumbs()
	{
		if (null === $this->breadcrumbs) {
			$this->breadcrumbs = new Breadcrumbs($this->get('translator'), $this->get('router'));
		}
		return $this->breadcrumbs;
	}
	
	/**
	 * @return AppTextRepository
	 */
	public function getTextRepository()
	{
		return $this->get('cantiga.core.repo.text');
	}
	
	/**
	 * @return ProjectFormRepository
	 */
	public function getFormRepository()
	{
		return $this->get('cantiga.core.forms');
	}
	
	/**
	 * @return ProjectSettings
	 */
	public function getProjectSettings()
	{
		return $this->get('cantiga.project.settings');
	}
	
	/**
	 * @return ExtensionPointsInterface
	 */
	public function getExtensionPoints()
	{
		return $this->get('cantiga.extensions');
	}
	
	/**
	 * Returns a new instance of data routes, a route builder for data sets that are sent via AJAX
	 * to the JS code. In this way, we don't have to generate the links in JavaScript, but we get
	 * ready links there.
	 * 
	 * @return DataRoutes
	 */
	public function dataRoutes()
	{
		return new DataRoutes($this->get('router'));
	}
	
	public function trans($string, array $args = [], $domain = null)
	{
		if (null === $this->translator) {
			$this->translator = $this->get('translator');
		}
		return $this->translator->trans($string, $args, $domain, $this->locale);
	}
	
	/**
	 * @return TranslatorInterface
	 */
	public function getTranslator()
	{
		if (null === $this->translator) {
			$this->translator = $this->get('translator');
		}
		return $this->translator;
	}
	
	public function newCrudInfo($repositoryService)
	{
		$info = new CRUDInfo();
		
		if (is_object($repositoryService)) {
			$info->setRepository($repositoryService);
		} else {
			$info->setRepository($this->get($repositoryService));
		}
		return $info;
	}
	
	public function showPageWithMessage($message, $page, array $args = array())
	{
		$this->get('session')->getFlashBag()->add('info', $message);
		return $this->redirect($this->generateUrl($page, $args));
	}
	
	public function showPageWithError($message, $page, array $args = array())
	{
		$this->get('session')->getFlashBag()->add('error', $message);
		return $this->redirect($this->generateUrl($page, $args));
	}
	
	public function hasRole($role)
	{
		return $this->get('security.authorization_checker')->isGranted($role) === true;
	}
	
	public function isGranted($attribute, $entity = null)
	{
		return $this->get('security.authorization_checker')->isGranted($attribute, $entity);
	}
	
	/**
	 * @return ExtensionPointFilter
	 */
	public function getExtensionPointFilter()
	{
		return new ExtensionPointFilter();
	}
}
