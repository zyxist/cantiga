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

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Unfinished implementation of the new authentication core based on {@link Cantiga\Metamodel\MembershipToken}.
 * Currently, the way the membership token is produced is extremely ugly, and it must be done right at some
 * point of development.
 *
 * @author Tomasz Jędrzejewski
 */
class MembershipAuthProvider implements AuthenticationProviderInterface
{
	private $userProvider;

	public function __construct(UserProviderInterface $userProvider, $cacheDir)
	{
		$this->userProvider = $userProvider;
	}

	public function authenticate(TokenInterface $token)
	{
	}

	public function supports(TokenInterface $token)
	{
	}
}
