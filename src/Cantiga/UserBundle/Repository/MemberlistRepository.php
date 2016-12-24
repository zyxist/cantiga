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
namespace Cantiga\UserBundle\Repository;

use Cantiga\Components\Hierarchy\Entity\AbstractProfileView;
use Cantiga\Components\Hierarchy\Entity\ExternalMember;
use Cantiga\Components\Hierarchy\Entity\Member;
use Cantiga\Components\Hierarchy\Entity\MemberInfo;
use Cantiga\Components\Hierarchy\Entity\PlaceRef;
use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\Components\Hierarchy\MembershipEntityInterface;
use Cantiga\Components\Hierarchy\MembershipRoleResolverInterface;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Transaction;
use Cantiga\UserBundle\Database\MemberlistAdapter;
use Exception;

/**
 * Fetches the data for the project/group/area member lists, and allows viewing associated user profiles.
 */
class MemberlistRepository
{
	/**
	 * @var MemberlistAdapter 
	 */
	protected $adapter;
	/**
	 * @var Transaction
	 */
	protected $transaction;
	/**
	 * @var MembershipRoleResolverInterface
	 */
	protected $roleResolver;
	
	public function __construct(MemberlistAdapter $adapter, Transaction $transaction, MembershipRoleResolverInterface $roleResolver)
	{
		$this->adapter = $adapter;
		$this->transaction = $transaction;
		$this->roleResolver = $roleResolver;
	}
		
	/**
	 * @param $membershipEntity Entity whose members we want to view
	 * @return array
	 */
	public function findMembers(MembershipEntityInterface $membershipEntity)
	{
		return $membershipEntity->findMembers($this->adapter->getConnection(), $this->roleResolver);
	}
	
	/**
	 * Finds the member of one of the places associated with the current project. Returns either
	 * {@link Member} or {@link ExternalMember} instances, depending on the relationship of the
	 * user to the current place.
	 * 
	 * @param $project Current project
	 * @param $id Member ID
	 * @return Member
	 * @throws ItemNotFoundException Member not found.
	 */
	public function getItem(HierarchicalInterface $project, MembershipEntityInterface $currentPlace, int $id): AbstractProfileView
	{
		$this->transaction->requestTransaction();
		try {
			return $this->loadMember($project, $currentPlace, $id);
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	private function loadMember(HierarchicalInterface $project, MembershipEntityInterface $currentPlace, int $id): AbstractProfileView
	{
		$projectPlaceId = $project->getPlace()->getId();
		$member = $this->adapter->getUserProfile($id, $projectPlaceId);
		$associatedProjectPlaces = $this->adapter->findUserPlaces($id, $projectPlaceId);
		if (empty($member) || sizeof($associatedProjectPlaces) == 0) {
			throw new ItemNotFoundException('The specified member has not been found.');
		}
		$places = [];
		$currentMemberInfo = null;
		foreach ($associatedProjectPlaces as $p) {
			if ($p['id'] == $currentPlace->getId()) {
				$currentMemberInfo = new MemberInfo($this->roleResolver->getRole($p['type'], $p['role']), $p['note'], (bool) $p['showDownstreamContactData']);
			}
			$places[] = new PlaceRef($p['id'], $p['name'], $p['type'], $p['slug'], (bool) $p['archived'], $this->roleResolver->getRole($p['type'], $p['role']), $p['note'], (bool) $p['showDownstreamContactData']);
		}
		
		if (null === $currentMemberInfo) {
			return new ExternalMember($member, $places);
		} else {
			return new Member($member, $currentMemberInfo, $places);
		}
	}
}
