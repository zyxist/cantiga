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

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Components\Hierarchy\MembershipEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\QueryClause;
use Doctrine\DBAL\Connection;
use WIO\EdkBundle\EdkTables;

/**
 * Allows sending the messages to the areas.
 */
class EdkMessage implements IdentifiableInterface, InsertableEntityInterface
{
	const STATUS_NEW = 0;
	const STATUS_ANSWERING = 1;
	const STATUS_COMPLETED = 2;
	const STATUS_CLOSED = 3;
	
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
	
	public static function fetchByRoot(Connection $conn, $id, MembershipEntityInterface $root)
	{
		if ($root instanceof Area) {
			$data = $conn->fetchAssoc('SELECT * FROM `'.EdkTables::MESSAGE_TBL.'` WHERE `id` = :id AND `areaId` = :rootId', [':id' => $id, ':rootId' => $root->getId()]);
		} elseif ($root instanceof Group) {
			$data = $conn->fetchAssoc('SELECT m.* FROM `'.EdkTables::MESSAGE_TBL.'` m '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.`id` = m.`areaId` '
				. 'WHERE m.`id` = :id AND a.`groupId` = :rootId', [':id' => $id, ':rootId' => $root->getId()]);
		} elseif ($root instanceof Project) {
			$data = $conn->fetchAssoc('SELECT m.* FROM `'.EdkTables::MESSAGE_TBL.'` m '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.`id` = m.`areaId` '
				. 'WHERE m.`id` = :id AND a.`projectId` = :rootId', [':id' => $id, ':rootId' => $root->getId()]);
		}
		if (false === $data) {
			return false;
		}
		$item = self::fromArray($data);
		
		if ($root instanceof Area) {
			$item->area = $root;
		} elseif ($root instanceof Group) {
			$item->area = Area::fetchByGroup($conn, $data['areaId'], $root);
		} elseif ($root instanceof Project) {
			$item->area = Area::fetchByProject($conn, $data['areaId'], $root);
		}
		if (!empty($data['responderId'])) {
			$item->responder = User::fetchByCriteria($conn, QueryClause::clause('u.id = :id', ':id', $data['responderId']));
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
	
	public function getContentFormatted()
	{
		return nl2br(htmlspecialchars($this->content));
	}
	
	public function getStatusText()
	{
		return self::statusText($this->status);
	}
	
	public function getStatusLabel()
	{
		return self::statusLabel($this->status);
	}
	
	public function getAllowedTransitions(User $currentUser, $additionalPermissionsGranted)
	{
		$transitions = [];
		if ($this->status == self::STATUS_NEW) {
			$transitions[] = ['name' => 'MsgTransitionTakeMessage', 'label' => 'primary', 'status' => 1, 'help' => 'MsgTransitionTakeMessageHelp'];
			if ($additionalPermissionsGranted) {
				$transitions[] = ['name' => 'MsgTransitionClose', 'label' => 'danger', 'status' => 3, 'help' => 'MsgTransitionCloseHelp'];
			}
		} elseif ($this->status == self::STATUS_ANSWERING) {
			if ($currentUser->getId() == $this->responder->getId() || $additionalPermissionsGranted) {
				$transitions[] = ['name' => 'MsgTransitionComplete', 'label' => 'primary', 'status' => 2, 'help' => 'MsgTransitionCompleteHelp'];
			}
			if ($additionalPermissionsGranted) {
				$transitions[] = ['name' => 'MsgTransitionReset', 'label' => 'danger', 'status' => 0, 'help' => 'MsgTransitionResetHelp'];
			}
		} elseif ($this->status == self::STATUS_COMPLETED) {
			if ($currentUser->getId() == $this->responder->getId() || $additionalPermissionsGranted) {
				$transitions[] = ['name' => 'MsgTransitionUndo', 'label' => 'warning', 'status' => 1, 'help' => 'MsgTransitionUndoHelp'];
			}
			if ($additionalPermissionsGranted) {
				$transitions[] = ['name' => 'MsgTransitionReset', 'label' => 'danger', 'status' => 0, 'help' => 'MsgTransitionResetHelp'];
			}
		} elseif ($this->status == self::STATUS_CLOSED) {
			if ($additionalPermissionsGranted) {
				$transitions[] = ['name' => 'MsgTransitionReset', 'label' => 'danger', 'status' => 0, 'help' => 'MsgTransitionResetHelp'];
			}
		}
		return $transitions;
	}
	
	public function getCurrentStatusDescription(User $currentUser)
	{
		if ($this->status == self::STATUS_NEW) {
			return 'MsgCurrentStatusNewDesc';
		} elseif ($this->status == self::STATUS_ANSWERING && $this->responder->getId() != $currentUser->getId()) {
			return 'MsgCurrentStatusAnsweringOtherUserDesc';
		} elseif ($this->status == self::STATUS_ANSWERING && $this->responder->getId() == $currentUser->getId()) {
			return 'MsgCurrentStatusAnsweringYouDesc';
		} elseif ($this->status == self::STATUS_COMPLETED && $this->responder->getId() != $currentUser->getId()) {
			return 'MsgCurrentStatusCompletedOtherUserDesc';
		} elseif ($this->status == self::STATUS_COMPLETED && $this->responder->getId() == $currentUser->getId()) {
			return 'MsgCurrentStatusCompletedYouDesc';
		} elseif ($this->status == self::STATUS_CLOSED && $this->responder->getId() != $currentUser->getId()) {
			return 'MsgCurrentStatusClosedOtherUserDesc';
		} elseif ($this->status == self::STATUS_CLOSED && $this->responder->getId() == $currentUser->getId()) {
			return 'MsgCurrentStatusClosedYouDesc';
		}
	}
	
	public function performTransition(User $currentUser, $additionalPermissionsGranted, $newStatus)
	{
		if ($this->status == self::STATUS_NEW) {
			if ($newStatus == self::STATUS_ANSWERING) {
				$this->responder = $currentUser;
				$this->answeredAt = time();
				$this->completedAt = null;
				$this->status = $newStatus;
				return;
			} elseif ($newStatus == self::STATUS_CLOSED && $additionalPermissionsGranted) {
				$this->responder = $currentUser;
				$this->completedAt = time();
				$this->status = $newStatus;
				return;
			}
		} elseif ($this->status == self::STATUS_ANSWERING) {
			if ($newStatus == self::STATUS_COMPLETED && $currentUser->getId() == $this->responder->getId()) {
				$this->completedAt = time();
				$this->status = $newStatus;
				return;
			} elseif ($newStatus == self::STATUS_NEW && $additionalPermissionsGranted) {
				$this->responder = null;
				$this->answeredAt = null;
				$this->completedAt = null;
				$this->status = $newStatus;
				return;
			}
		} elseif ($this->status == self::STATUS_COMPLETED) {
			if ($newStatus == self::STATUS_ANSWERING && ($currentUser->getId() == $this->responder->getId() || $additionalPermissionsGranted)) {
				$this->completedAt = null;
				$this->status = $newStatus;
				return;
			} elseif ($newStatus == self::STATUS_NEW && $additionalPermissionsGranted) {
				$this->responder = null;
				$this->answeredAt = null;
				$this->completedAt = null;
				$this->status = $newStatus;
				return;
			}
		} elseif ($this->status == self::STATUS_CLOSED) {
			if ($newStatus == self::STATUS_NEW && $additionalPermissionsGranted) {
				$this->responder = null;
				$this->answeredAt = null;
				$this->completedAt = null;
				$this->status = $newStatus;
				return;
			}
		}
		throw new ModelException('This status transition is not allowed.');
	}
	
	public static function statusLabel($status)
	{
		switch($status) {
			case self::STATUS_NEW:
				return 'danger';
			case self::STATUS_ANSWERING:
				return 'primary';
			case self::STATUS_COMPLETED:
				return 'success';
			case self::STATUS_CLOSED:
				return 'default';
		}
	}
	
	public static function statusText($status)
	{
		switch ($status) {
			case self::STATUS_NEW:
				return 'MsgStatusNew';
			case self::STATUS_ANSWERING:
				return 'MsgStatusAnswering';
			case self::STATUS_COMPLETED:
				return 'MsgStatusCompleted';
			case self::STATUS_CLOSED:
				return 'MsgStatusClosed';
		}
	}
	
	public static function getStatusList()
	{
		return [
			self::STATUS_NEW => 'MsgStatusNew',
			self::STATUS_ANSWERING => 'MsgStatusAnswering',
			self::STATUS_COMPLETED => 'MsgStatusCompleted',
			self::STATUS_CLOSED => 'MsgStatusClosed',
		];
	}
		
	public function insert(Connection $conn)
	{
		$this->createdAt = time();		
		$conn->insert(EdkTables::MESSAGE_TBL, DataMappers::pick($this,
			['area', 'subject', 'content', 'authorName', 'authorEmail', 'authorPhone', 'createdAt', 'ipAddress']
		));
		return $conn->lastInsertId();
	}
	
	public function changeState(Connection $conn)
	{
		$conn->update(EdkTables::MESSAGE_TBL, DataMappers::pick($this, ['status', 'answeredAt', 'completedAt', 'responder', 'duplicate']), ['id' => $this->id]);
	}
}
