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
namespace Cantiga\CoreBundle\Api\Controller;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Breadcrumbs;
use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointFilter;
use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointsInterface;
use Cantiga\CoreBundle\Repository\AppTextRepository;
use Cantiga\CoreBundle\Repository\ProjectFormRepository;
use Cantiga\CoreBundle\Settings\ProjectSettings;
use Cantiga\Metamodel\DataRoutes;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Extends the default Symfony controller with additional useful methods and shortcuts for
 * the most common operations. Also, provides a compatibility layer for methods that were
 * public in Symfony 2.x, but are protected since Symfony 3.0. A lot of our code relies
 * on that, and until we develop a different solution, this must remain in the old way.
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
	
	public function newCrudInfo($repositoryService): CRUDInfo
	{
		$info = new CRUDInfo();
		
		if (is_object($repositoryService)) {
			$info->setRepository($repositoryService);
		} else {
			$info->setRepository($this->get($repositoryService));
		}
		return $info;
	}
	
	public function showPageWithMessage($message, $page, array $args = array()): Response
	{
		$this->get('session')->getFlashBag()->add('info', $message);
		return $this->redirect($this->generateUrl($page, $args));
	}
	
	public function showPageWithError($message, $page, array $args = array()): Response
	{
		$this->get('session')->getFlashBag()->add('error', $message);
		return $this->redirect($this->generateUrl($page, $args));
	}
	
	/**
	 * Show a page with some message for the user, without redirecting him anywhere.
	 * 
	 * @param string $title Page title
	 * @param string $message Message to display
	 * @return Response
	 */
	public function showMessage(string $title, string $message): Response
	{
		return $this->render('CantigaCoreBundle:layout:message.html.twig', array(
			'pageTitle' => $title,
			'pageSubtitle' => '',
			'messageTitle' => $this->trans('Message', [], 'general'),
			'message' => $message
		));
	}
	
	public function hasRole($role): bool
	{
		return $this->get('security.authorization_checker')->isGranted($role) === true;
	}
	
	/**
	 * @return ExtensionPointFilter
	 */
	public function getExtensionPointFilter()
	{
		return new ExtensionPointFilter();
	}
	
	// ----------------------
	// All the methods below come from the default Symfony controller, but we change
	// the access to public, like in Symfony 2.x, because a lot of actions rely on this.
	// Until we find and develop a better way to handle this, let's use the old way.
	// ----------------------
	
	public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
	{
		return parent::generateUrl($route, $parameters, $referenceType);
	}
	
	public function forward($controller, array $path = array(), array $query = array())
	{
		return parent::forward($controller, $path, $query);
	}
	
	public function redirect($url, $status = 302)
	{
		return parent::redirect($url, $status);
	}

	public function redirectToRoute($route, array $parameters = array(), $status = 302)
	{
		return parent::redirectToRoute($route, $parameters, $status);
	}
	
	public function addFlash($type, $message)
	{
		return parent::addFlash($type, $message);
	}
	
	public function isGranted($attributes, $object = null)
	{
		return parent::isGranted($attributes, $object);
	}
	
	public function denyAccessUnlessGranted($attributes, $object = null, $message = 'Access Denied.')
	{
		return parent::denyAccessUnlessGranted($attributes, $object, $message);
	}
	
	public function renderView($view, array $parameters = array())
	{
		return parent::renderView($view, $parameters);
	}
	
	public function render($view, array $parameters = array(), Response $response = null)
	{
		return parent::render($view, $parameters, $response);
	}
	
	public function stream($view, array $parameters = array(), StreamedResponse $response = null)
	{
		return parent::stream($view, $parameters, $response);
	}
	
	public function createNotFoundException($message = 'Not Found', Exception $previous = null)
	{
		return parent::createNotFoundException($message, $previous);
	}
	
	public function createAccessDeniedException($message = 'Access Denied.', Exception $previous = null)
	{
		return parent::createAccessDeniedException($message, $previous);
	}
	
	public function createForm($type, $data = null, array $options = array())
	{
		return parent::createForm($type, $data, $options);
	}
	
	public function createFormBuilder($data = null, array $options = array())
	{
		return parent::createFormBuilder($data, $options);
	}
	
	public function getUser()
	{
		return parent::getUser();
	}
	
	public function has($id)
	{
		return parent::has($id);
	}
	
	public function get($id)
	{
		return parent::get($id);
	}
	
	public function getParameter($name)
	{
		return parent::getParameter($name);
	}
	
	public function isCsrfTokenValid($id, $token)
	{
		return parent::isCsrfTokenValid($id, $token);
	}
}
