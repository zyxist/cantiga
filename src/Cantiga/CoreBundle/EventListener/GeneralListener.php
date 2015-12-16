<?php
namespace Cantiga\CoreBundle\EventListener;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Exception\AreasNotSupportedException;
use Cantiga\Metamodel\TimeFormatter;
use Cantiga\Metamodel\Transaction;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * @author Tomasz Jędrzejewski
 */
class GeneralListener
{
	const FALLBACK_LOCALE = 'en';
	
	/**
	 * @var AuthorizationCheckerInterface 
	 */
    private $authChecker;
	/**
	 * @var TokenStorageInterface 
	 */
	private $tokenStorage;
	/**
	 * @var Transaction 
	 */
	private $transaction;
	/**
	 * @var Session
	 */
	private $session;
	/**
	 * @var TimeFormatter
	 */
	private $timeFormatter;

	public function __construct(AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage, Transaction $transaction, Session $session, TimeFormatter $timeFormatter)
	{
		$this->authChecker = $authChecker;
		$this->tokenStorage = $tokenStorage;
		$this->transaction = $transaction;
		$this->session = $session;
		$this->timeFormatter = $timeFormatter;
	}
	
    public function onKernelRequest(GetResponseEvent $event)
	{
		$request = $event->getRequest();		
		// locale detection - request, then query path, if the controller allows to do so, and session.
		if ($locale = $request->attributes->get('_locale')) {
			$request->getSession()->set('_locale', $locale);
		} else if($request->attributes->get('_localeFromQuery') && $request->query->has('_locale')) {
			$request->getSession()->set('_locale', $request->query->get('_locale', self::FALLBACK_LOCALE));
		}
		$request->setLocale($request->getSession()->get('_locale'));
	}

	public function onKernelController(FilterControllerEvent $event)
	{
		$controller = $event->getController();

		if(!is_array($controller)) {
			// not a object but a different kind of callable. Do nothing
			return;
		}

		$controllerObject = $controller[0];
		if($controllerObject instanceof CantigaController) {
			// set the time formatting settings
			$controllerObject->get('translator')->setLocale($event->getRequest()->getLocale());
			$this->timeFormatter->configure($controllerObject->get('translator'), $event->getRequest()->getLocale(), $event->getRequest()->getSession()->get('timezone'));
			$controllerObject->get('cantiga.locale')->setLocale($event->getRequest()->getLocale());
			
			// initialize the controller
			$potentialResponse = $controllerObject->initialize($event->getRequest(), $this->authChecker);
			if (!empty($potentialResponse)) {
				$event->setController(function() use($potentialResponse) {
					return $potentialResponse;
				});
			}
		}
	}
	
	/**
	 * Make sure that the transaction is always SOMEHOW closed at the end of the request.
	 * 
	 * @param FilterResponseEvent $event
	 */
	public function onKernelResponse(FilterResponseEvent $event)
	{
		if($event->isMasterRequest()) {
			$this->transaction->closeTransaction();
		}
	}
	
	public function onInteractiveLogin(InteractiveLoginEvent $event)
	{
		$user = $this->tokenStorage->getToken()->getUser();
		$timezone = $user->getSettingsTimezone();
		if (empty($timezone)) {
			$timezone = 'UTC';
		}
		$this->session->set('timezone', $timezone);
		
		$locale = $user->getSettingsLanguage()->getLocale();
		if (empty($locale)) {
			$locale = 'en_EN'; // fallback to English in case of some misconfiguration
		}
		$this->session->set('_locale', $locale);
	}
}
