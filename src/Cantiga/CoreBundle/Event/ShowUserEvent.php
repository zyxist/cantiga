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

namespace Cantiga\CoreBundle\Event;

use Cantiga\Components\Hierarchy\Entity\Membership;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Emitted by the navbar controller, the event is used to pass the info about the currently
 * logged user, so that we can display a profile information for him/her.
 */
class ShowUserEvent extends Event
{

	/**
	 * @var UserInterface
	 */
	protected $user;
	/**
	 * @var Membership
	 */
	protected $membership;

	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}

	public function getUser()
	{
		return $this->user;
	}
	
	public function setMembership(Membership $membership)
	{
		$this->membership = $membership;
	}
	
	public function getMembership()
	{
		return $this->membership;
	}

}
