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

namespace Cantiga\CoreBundle\EventListener;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\Metamodel\LocaleProvider;
use Cantiga\Metamodel\TimeFormatter;
use Cantiga\Metamodel\Transaction;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Tomasz Jędrzejewski
 */
class GeneralListener
{

	const FALLBACK_LOCALE = 'en';
	const LAST_LANG_COOKIE = 'cc-last-lang';
	const DEF_LAST_LANG_TIME = 2592000; // 30 days

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
	/**
	 * @var TranslatorInterface
	 */
	private $translator;
	/**
	 * @var LocaleProvider
	 */
	private $localeProvider;
	
	public function __construct(AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage, Transaction $transaction, Session $session, TimeFormatter $timeFormatter, TranslatorInterface $translator, LocaleProvider $localeProvider)
	{
		$this->authChecker = $authChecker;
		$this->tokenStorage = $tokenStorage;
		$this->transaction = $transaction;
		$this->session = $session;
		$this->timeFormatter = $timeFormatter;
		$this->translator = $translator;
		$this->localeProvider = $localeProvider;
	}

	public function onKernelRequest(GetResponseEvent $event)
	{
		$request = $event->getRequest();
		// locale detection - request, then query path, if the controller allows to do so, and session.
		if ($locale = $request->attributes->get('_locale')) {
			$request->getSession()->set('_locale', $locale);
		} else if ($request->attributes->get('_localeFromQuery') && $request->query->has('_locale')) {
			$request->getSession()->set('_locale', $request->query->get('_locale', self::FALLBACK_LOCALE));
		} else if ($request->getSession()->has('_user_locale')) {
			$request->getSession()->set('_locale', $request->getSession()->get('_user_locale'));
		} else if ($request->cookies->has(self::LAST_LANG_COOKIE)) {
			$request->getSession()->set('_locale', $request->cookies->get(self::LAST_LANG_COOKIE));
		}
		$request->setLocale($request->getSession()->get('_locale'));
	}

	public function onKernelController(FilterControllerEvent $event)
	{
		$controller = $event->getController();

		if (!is_array($controller)) {
			// not a object but a different kind of callable. Do nothing
			return;
		}

		$controllerObject = $controller[0];
		if ($controllerObject instanceof CantigaController) {
			// set the time formatting settings
			$this->translator->setLocale($event->getRequest()->getLocale());
			$this->timeFormatter->configure($this->translator, $event->getRequest()->getLocale(), $event->getRequest()->getSession()->get('timezone'));
			$this->localeProvider->setLocale($event->getRequest()->getLocale());

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
		if ($event->isMasterRequest()) {
			$this->transaction->closeTransaction();
		}
		if ($event->getRequest()->getSession()->has('_locale')) {
			$event->getResponse()->headers->setCookie(new Cookie(self::LAST_LANG_COOKIE, $event->getRequest()->getSession()->get('_locale'), time() + self::DEF_LAST_LANG_TIME));
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
			$locale = 'en'; // fallback to English in case of some misconfiguration
		}
		$this->session->set('_user_locale', $locale);
	}

}
