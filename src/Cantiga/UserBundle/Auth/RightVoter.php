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

use Cantiga\Components\Hierarchy\Entity\AbstractProfileView;
use Cantiga\Components\Hierarchy\Entity\ExternalMember;
use Cantiga\Components\Hierarchy\Entity\Member;
use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\Components\Hierarchy\MembershipStorageInterface;
use Cantiga\UserBundle\Database\MemberlistAdapter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Checks the user rights to perform certain activities as a member of the current place.
 */
class RightVoter extends Voter
{
	const RIGHT_VIEW_CONTACT = 'RIGHT_VIEW_CONTACT_DATA';
	private $membershipStorage;
	private $adapter;
	
	private $groupCache = [];
	
	public function __construct(MembershipStorageInterface $membershipStorage, MemberlistAdapter $adapter)
	{
		$this->membershipStorage = $membershipStorage;
		$this->adapter = $adapter;
	}
	
	protected function supports($attribute, $subject): bool
	{
		return $attribute == self::RIGHT_VIEW_CONTACT && ($subject instanceof AbstractProfileView);
	}
	
	protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
	{
		if (!$this->membershipStorage->hasMembership() || !$token->isAuthenticated()) {
			return false;
		}
		
		if ($subject instanceof Member) {
			return true;
		} elseif ($subject instanceof ExternalMember) {
			return $this->resolveExternalMember($subject, $this->membershipStorage->getMembership());
		}
	}
	
	private function resolveExternalMember(ExternalMember $member, Membership $viewer): bool
	{
		switch ($viewer->getPlace()->getTypeName()) {
			case 'Project':
				return $viewer->getShowDownstreamContactData();
			case 'Group':
				if ($viewer->getShowDownstreamContactData()) {
					return $this->checkMemberofAreas($member, $viewer);
				}
				return false;
			case 'Area':
				return false;
		}
	}
	
	private function checkMemberofAreas($member, $viewer): bool
	{
		$groupId = $viewer->getPlace()->getId();
		if (!isset($this->groupCache[$groupId])) {
			$this->groupCache[$groupId] = $this->adapter->findAreaPlaceIds($groupId);
		}
		$cache = $this->groupCache[$groupId];
		foreach ($member->getPlaces() as $place) {
			if ($place->getType() == 'Area' && in_array($place->getId(), $cache)) {
				return true;
			}
		}
		return false;
	}
}
