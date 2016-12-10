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
namespace Cantiga\UserBundle\Membership;

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\Components\Hierarchy\Entity\PlaceRef;
use Cantiga\Components\Hierarchy\MembershipFinderInterface;
use Cantiga\Components\Hierarchy\PlaceLoaderInterface;
use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;
use Cantiga\UserBundle\Repository\MembershipRepository;
use Symfony\Component\HttpFoundation\Request;

class MembershipFinder implements MembershipFinderInterface
{
	const SLUG_PROP = 'slug';
	
	private $membershipRepository;
	private $membershipStorage;
	private $placeLoaders = [];
	
	public function __construct(MembershipStorage $storage, MembershipRepository $repository)
	{
		$this->membershipStorage = $storage;
		$this->membershipRepository = $repository;
	}
	
	public function addPlaceLoader(string $type, PlaceLoaderInterface $loader)
	{
		$this->placeLoaders[$type] = $loader;
	}

	public function findMembership(CantigaUserRefInterface $user, Request $request)
	{
		$places = $this->membershipRepository->getUserPlaces($user);
		$this->membershipStorage->setPlaces($places);
		$membership = $this->choosePlace($places, $request);
		if (null !== $membership) {
			$this->membershipStorage->setMembership($membership);
		}
		return $membership;
	}
	
	private function choosePlace(array $places, Request $request)
	{
		if (!$request->attributes->has(self::SLUG_PROP)) {
			return null;
		}
		$slug = $request->attributes->get(self::SLUG_PROP);
		foreach ($places as $place) {
			if ($place->getSlug() == $slug) {
				
				return $this->createMembership($place);
			}
		}
		return null;
	}
	
	private function createMembership(PlaceRef $place): Membership
	{
		if (!isset($this->placeLoaders[$place->getType()])) {
			throw new \LogicException('No loader for place \''.$place->getType().'\'');
		}
		return new Membership(
			$this->placeLoaders[$place->getType()]->loadPlace($place),
			$place->getRole(),
			$place->getNote(),
			$place->getShowDownstreamContactData()
		);
	}
}
