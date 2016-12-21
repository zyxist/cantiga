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

use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;

/**
 * Details about somebody's profile.
 */
abstract class AbstractProfileView implements CantigaUserRefInterface
{
	private $id;
	private $name;
	private $avatar;
	private $lastVisit;
	private $location;
	private $contactMail;
	private $contactTelephone;
	private $notes;
	
	public function __construct(array $data)
	{
		$this->id = (int) $data['id'] ?? null;
		$this->name = $data['name'] ?? null;
		$this->avatar = $data['avatar'] ?? null;
		$this->lastVisit = (int) $data['lastVisit'] ?? null;
		$this->location = $data['location'] ?? null;
		$this->contactMail = $data['contactMail'] ?? null;
		$this->contactTelephone = $data['contactTelephone'] ?? null;
		$this->notes = $data['notes'] ?? null;
	}
	
	public function getId(): int
	{
		return $this->id;
	}
	
	public function getName(): string
	{
		return $this->name;
	}
	
	public function getAvatar()
	{
		return $this->avatar;
	}
	
	public function getLastVisit()
	{
		return $this->lastVisit;
	}
	
	public function getLocation()
	{
		return $this->location;
	}
	
	public function getContactMail()
	{
		return $this->contactMail;
	}
	
	public function getContactTelephone()
	{
		return $this->contactTelephone;
	}
	
	public function getNotes()
	{
		return $this->notes;
	}
	
	public function asArray(): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'avatar' => $this->avatar,
			'lastVisit' => $this->lastVisit,
			'location' => $this->location,
			'contactMail' => $this->contactMail,
			'contactTelephone' => $this->contactTelephone,
			'userNotes' => $this->notes,
		];
	}
}
