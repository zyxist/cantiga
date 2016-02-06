<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\QueryClause;
use Doctrine\DBAL\Connection;
use WIO\EdkBundle\EdkTables;

/**
 * Allows sending the messages to the areas.
 *
 * @author Tomasz Jędrzejewski
 */
class EdkMessage implements IdentifiableInterface, InsertableEntityInterface
{
	const STATE_NEW = 0;
	const STATE_ANSWERING = 1;
	const STATE_COMPLETED = 2;
	
	private $id;
	private $area;
	private $subject;
	private $content;
	private $authorName;
	private $authorEmail;
	private $authorPhone;
	private $createdAt;
	private $answeredAt;
	private $completedAt;
	private $status;
	private $responder;
	private $duplicate;
	private $ipAddress;
	
	public static function fetchByArea(Connection $conn, $id, Area $area)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.EdkTables::MESSAGE_TBL.'` WHERE `routeId` = :id', [':id' => $route->getId()]);
		if (false === $data) {
			return false;
		}
		$item = self::fromArray($data);
		$item->area = $area;
		if (!empty($data['responderId'])) {
			$item->responder = User::fetchByCriteria($conn, QueryClause::clause('u.id = :id', [':id' => $data['responderId']]));
		}
		return $item;
	}
	
	public static function fromArray($array, $prefix = '')
	{
		$item = new EdkMessage;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public static function getRelationships()
	{
		return ['area', 'responder'];
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getArea()
	{
		return $this->area;
	}

	public function getSubject()
	{
		return $this->subject;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getAuthorName()
	{
		return $this->authorName;
	}

	public function getAuthorEmail()
	{
		return $this->authorEmail;
	}

	public function getAuthorPhone()
	{
		return $this->authorPhone;
	}

	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	public function getAnsweredAt()
	{
		return $this->answeredAt;
	}

	public function getCompletedAt()
	{
		return $this->completedAt;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getResponder()
	{
		return $this->responder;
	}

	public function getDuplicate()
	{
		return $this->duplicate;
	}
	
	public function getIpAddress()
	{
		return $this->ipAddress;
	}

	public function setId($id)
	{
		DataMappers::noOverwritingId($this->id);
		$this->id = $id;
		return $this;
	}

	public function setArea(Area $area)
	{
		DataMappers::noOverwritingField($this->area);
		$this->area = $area;
		return $this;
	}

	public function setSubject($subject)
	{
		$this->subject = $subject;
		return $this;
	}

	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	public function setAuthorName($authorName)
	{
		$this->authorName = $authorName;
		return $this;
	}

	public function setAuthorEmail($authorEmail)
	{
		$this->authorEmail = $authorEmail;
		return $this;
	}

	public function setAuthorPhone($authorPhone)
	{
		$this->authorPhone = $authorPhone;
		return $this;
	}

	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;
		return $this;
	}

	public function setAnsweredAt($answeredAt)
	{
		$this->answeredAt = $answeredAt;
		return $this;
	}

	public function setCompletedAt($completedAt)
	{
		$this->completedAt = $completedAt;
		return $this;
	}

	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	public function setResponder(User $responder = null)
	{
		$this->responder = $responder;
		return $this;
	}

	public function setDuplicate($duplicate)
	{
		$this->duplicate = $duplicate;
		return $this;
	}

	public function setIpAddress($ip)
	{
		$this->ipAddress = (int) $ip;
		return $this;
	}
		
	public function insert(Connection $conn)
	{
		$this->createdAt = time();		
		$conn->insert(EdkTables::MESSAGE_TBL, DataMappers::pick($this,
			['area', 'subject', 'content', 'authorName', 'authorEmail', 'authorPhone', 'createdAt', 'ipAddress']
		));
		return $conn->lastInsertId();
	}
}
