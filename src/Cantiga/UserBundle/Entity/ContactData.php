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
namespace Cantiga\UserBundle\Intent;

use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;
use Cantiga\CoreBundle\CoreTables;
use Doctrine\DBAL\Connection;

class ContactData
{
	private $id;
	private $name;
	private $email;
	private $telephone;
	private $notes;
	private $required;
	private $user;
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getUser(): CantigaUserRefInterface
	{
		return $this->user;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getContactEmail()
	{
		return $this->contactEmail;
	}

	public function getContactTelephone()
	{
		return $this->contactTelephone;
	}

	public function getContactNotes()
	{
		return $this->contactNotes;
	}

	public function getRequired()
	{
		return $this->required;
	}

	public function setId($id): self
	{
		$this->id = $id;
		return $this;
	}
	
	public function setUser(CantigaUserRefInterface $user): self
	{
		$this->user = $user;
		return $this;
	}

	public function setName($name): self
	{
		$this->name = $name;
		return $this;
	}

	public function setEmail($email): self
	{
		$this->email = $email;
		return $this;
	}

	public function setTelephone($telephone): self
	{
		$this->telephone = $telephone;
		return $this;
	}

	public function setNotes($notes): self
	{
		$this->notes = $notes;
		return $this;
	}
	
	public function setRequired($required): self
	{
		$this->required = $required;
		return $this;
	}
	
	public function update(Connection $conn)
	{
		$contacts = $conn->fetchAssoc('SELECT FOR UPDATE * FROM `'.CoreTables::CONTACT_TBL.'` WHERE `userId` = :userId AND `projectId` = :projectId', [':userId' => $this->getUser()->getId(), $this->id]);
		if (!empty($contacts)) {
			$conn->update(CoreTables::CONTACT_TBL, 
				['email' => $this->email, 'telephone' => $this->telephone, 'notes' => $this->notes], 
				['userId' => $this->getUser()->getId(), 'projectId' => $this->id]);
		} else {
			$conn->insert(CoreTables::CONTACT_TBL, [
				'email' => $this->email,
				'telephone' => $this->telephone,
				'notes' => $this->notes,
				'userId' => $this->user->getId(),
				'projectId' => $this->id]
			);
		}
	}
}
