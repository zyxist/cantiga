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
declare(strict_types=1);
namespace Cantiga\Components\Hierarchy\Entity;

use Cantiga\Components\Hierarchy\HierarchicalInterface;

/**
 * Description of Membership
 */
class Membership extends AbstractMemberInfo
{
	private $place;
	
	public function __construct(HierarchicalInterface $place = null, MembershipRole $role = null, string $note = '', bool $showDownstreamContactInfo = false)
	{
		parent::__construct($role, $note, $showDownstreamContactInfo);
		$this->place = $place;
	}
	
	public function getPlace(): HierarchicalInterface
	{
		return $this->place;
	}
}
