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

use Cantiga\Components\Hierarchy\PlaceRefInterface;

/**
 * Basic information of some place combined with the user membership.
 */
class PlaceRef extends AbstractMemberInfo implements PlaceRefInterface
{
	private $id;
	private $name;
	private $type;
	private $slug;
	private $archived;
	
	public function __construct(int $id, string $name, string $type, string $slug, bool $archived, MembershipRole $role = null, string $note = '', bool $showDownstreamContactInfo = false)
	{
		parent::__construct($role, $note, $showDownstreamContactInfo);
		$this->id = $id;
		$this->name = $name;
		$this->type = $type;
		$this->slug = $slug;
		$this->archived = $archived;
	}
	
	public function getId(): int
	{
		return $this->id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getSlug(): string
	{
		return $this->slug;
	}
	
	public function getArchived(): bool
	{
		return $this->archived;
	}
}
