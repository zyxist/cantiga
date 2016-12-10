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
namespace Cantiga\UserBundle\Auth;

use Cantiga\Components\Hierarchy\MembershipStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PlaceRoleVoter extends Voter
{
	const PREFIX = 'PLACE_';
	private $membershipStorage;
	
	public function __construct(MembershipStorageInterface $membershipStorage)
	{
		$this->membershipStorage = $membershipStorage;
	}
	
	protected function supports($attribute, $subject): bool
	{
		return (strpos($attribute, self::PREFIX) === 0);
	}

	protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
	{
		if (!$this->membershipStorage->hasMembership() || !$token->isAuthenticated()) {
			return false;
		}
		$membership = $this->membershipStorage->getMembership();
		return $membership->getRole()->isGranted($attribute);
	}
}
