<?php

/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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

namespace Cantiga\CoreBundle\Auth;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * Unfinished implementation of the new authentication core based on {@link Cantiga\Metamodel\MembershipToken}.
 * Currently, the way the membership token is produced is extremely ugly, and it must be done right at some
 * point of development.
 *
 * @author Tomasz Jędrzejewski
 */
class MembershipAuthListener implements ListenerInterface
{

	protected $tokenStorage;
	protected $authenticationManager;

	public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager)
	{
		$this->tokenStorage = $tokenStorage;
		$this->authenticationManager = $authenticationManager;
	}

	public function handle(GetResponseEvent $event)
	{
		$request = $event->getRequest();
		if (!($ml = $request->attributes->get('_membership_loader')) || !($slug = $request->get('slug'))) {
			return;
		}
	}

}
