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
namespace WIO\EdkBundle\Entity;

use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Doctrine\DBAL\Connection;
use WIO\EdkBundle\EdkTables;

/**
 * Removed participants leave a trail in the database, so that we know that someone has
 * been removed.
 *
 * @author Tomasz Jędrzejewski
 */
class EdkRemovedParticipant implements IdentifiableInterface, InsertableEntityInterface 
{
	private $id;
	private $participantId;
	private $area;
	private $email;
	private $reason;
	private $removedAt;
	/**
	 * For the removal time.
	 * @var EdkParticipant
	 */
	private $participant;
	
	public function __construct(EdkParticipant $participant = null)
	{
		if (null !== $participant) {
			$this->removedAt = time();
			$this->participantId = $participant->getId();
			$this->participant = $participant;
			$this->email = $participant->getEmail();
		}
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getParticipantId()
	{
		return $this->participantId;
	}

	public function getArea()
	{
		return $this->area;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getReason()
	{
		return $this->reason;
	}

	public function getRemovedAt()
	{
		return $this->removedAt;
	}

	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	public function setParticipantId($participantId)
	{
		$this->participantId = $participantId;
		return $this;
	}

	public function setArea($area)
	{
		$this->area = $area;
		return $this;
	}

	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	public function setReason($reason)
	{
		$this->reason = $reason;
		return $this;
	}

	public function setRemovedAt($removedAt)
	{
		$this->removedAt = $removedAt;
		return $this;
	}

	public function insert(Connection $conn)
	{
		$conn->insert(EdkTables::EDK_REMOVED_PARTICIPANT_TBL, array(
			'participantId' => $this->participantId,
			'areaId' => $this->area->getId(),
			'reason' => $this->reason,
			'email' => $this->email,
			'removedAt' => $this->removedAt
		));
		$this->id = $this->conn->lastInsertId();
		$this->participant->remove($conn);
	}
}
