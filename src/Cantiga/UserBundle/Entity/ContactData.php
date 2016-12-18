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
namespace Cantiga\UserBundle\Entity;
use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;
use Cantiga\CoreBundle\CoreTables;
use Doctrine\DBAL\Connection;

class ContactData
{
	private $project;
	private $user;
	private $email;
	private $telephone;
	private $notes;
	
	public static function findContactData(Connection $conn, HierarchicalInterface $project, CantigaUserRefInterface $user): ContactData
	{
		$contact = $conn->fetchAssoc('SELECT * FROM `'.CoreTables::CONTACT_TBL.'` WHERE `userId` = :userId AND `placeId` = :placeId FOR UPDATE', [
			':userId' => $user->getId(),
			':placeId' => $project->getPlace()->getId()
		]);
		
		if (empty($contact)) {
			return new ContactData($project, $user);
		}
		return new ContactData($project, $user, $contact);
	}
	
	public function __construct(HierarchicalInterface $project, CantigaUserRefInterface $user, array $data = [])
	{
		$this->project = $project;
		$this->user = $user;
		$this->email = $data['email'] ?? '';
		$this->telephone = $data['telephone'] ?? '';
		$this->notes = $data['notes'] ?? '';
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getUser(): CantigaUserRefInterface
	{
		return $this->user;
	}

	public function getProject(): HierarchicalInterface
	{
		return $this->project;
	}
	
	public function getEmail()
	{
		return $this->email;
	}

	public function getTelephone()
	{
		return $this->telephone;
	}

	public function getNotes()
	{
		return $this->notes;
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
	
	public function asArray(): array
	{
		return [
			'id' => $this->project->getPlace()->getId(),
			'name' => $this->project->getName(),
			'email' => $this->email,
			'telephone' => $this->telephone,
			'notes' => $this->notes,
			'required' => 1,
		];
	}
	
	public function update(Connection $conn)
	{
		$contacts = $conn->fetchAssoc('SELECT * FROM `'.CoreTables::CONTACT_TBL.'` WHERE `userId` = :userId AND `placeId` = :placeId FOR UPDATE', [
			':userId' => $this->getUser()->getId(),
			':placeId' => $this->getProject()->getPlace()->getId()
		]);
		if (!empty($contacts)) {
			$conn->update(CoreTables::CONTACT_TBL, 
				['email' => $this->email, 'telephone' => $this->telephone, 'notes' => $this->notes], 
				['userId' => $this->getUser()->getId(), 'placeId' => $this->getProject()->getPlace()->getId()]);
		} else {
			$conn->insert(CoreTables::CONTACT_TBL, [
				'email' => $this->email,
				'telephone' => $this->telephone,
				'notes' => $this->notes,
				'userId' => $this->user->getId(),
				'placeId' => $this->getProject()->getPlace()->getId()]
			);
		}
	}
}
