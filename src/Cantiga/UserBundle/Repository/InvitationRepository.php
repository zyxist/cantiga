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

use Cantiga\Components\Hierarchy\MembershipRepositoryInterface;
use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Invitation;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\CoreBundle\Event\InvitationEvent;
use Cantiga\CoreBundle\Event\UserEvent;
use Cantiga\Metamodel\Exception\DuplicateItemException;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This repository can be used for both viewing the invitation list by the users,
 * and sending new invitations from other places.
 */
class InvitationRepository
{
	/**
	 * @var Connection 
	 */
	private $conn;
	/**
	 * @var Transaction
	 */
	private $transaction;
	/**
	 * @var EventDispatcherInterface
	 */
	private $eventDispatcher;
	/**
	 * Repositories that could handle joining the new member for the particular entity type.
	 * @var array 
	 */
	private $repositories;
	
	public function __construct(Connection $conn, Transaction $transaction, EventDispatcherInterface $eventDispatcher)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->eventDispatcher = $eventDispatcher;
	}
	
	public function registerRepository($entity, MembershipRepositoryInterface $repository)
	{
		$this->repositories[$entity] = $repository;
	}
	
	public function countInvitations(User $user)
	{
		return $this->conn->fetchColumn('SELECT COUNT(`id`) FROM `'.CoreTables::INVITATION_TBL.'` WHERE `userId` = :id', [':id' => $user->getId()]);
	}
	
	public function invite(Invitation $invitation)
	{
		$this->transaction->requestTransaction();
		try {
			$id = $invitation->insert($this->conn);
			$this->eventDispatcher->dispatch(CantigaEvents::INVITATION_CREATED, new InvitationEvent($invitation));
			return $id;
		} catch (UniqueConstraintViolationException $exception) {
			$this->transaction->requestRollback();
			throw new DuplicateItemException('PersonAlreadyInvitedErr');
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	/**
	 * Shows all invitations for the given user.
	 * 
	 * @param \Cantiga\CoreBundle\Repository\User $user
	 */
	public function findInvitations(User $user)
	{
		return $this->conn->fetchAll('SELECT i.`id`, i.`createdAt`, i.`note`, u.`name` AS `inviterName`, p.`name` AS `placeName`, p.`type` AS `placeType` '
			. 'FROM `'.CoreTables::INVITATION_TBL.'` i '
			. 'INNER JOIN `'.CoreTables::PLACE_TBL.'` p ON p.`id` = i.`placeId` '
			. 'INNER JOIN `'.CoreTables::USER_TBL.'` u ON u.`id` = i.`inviterId` '
			. 'WHERE i.`userId` = :userId '
			. 'ORDER BY i.`id` DESC', [':userId' => $user->getId()]);
	}
	
	/**
	 * Finds the invitation of the given ID. An exception is thrown, if the invitation does not exist.
	 * 
	 * @param int $id ID
	 * @param CantigaUserRefInterface $user Invited user.
	 * @return Found invitation
	 */
	public function getItem($id, CantigaUserRefInterface $user): Invitation
	{
		$this->transaction->requestTransaction();
		try {
			$item = Invitation::fetchByUser($this->conn, $id, $user);
			if (false === $item) {
				throw new ItemNotFoundException('The specified invitation has not been found.', $id);
			}
			return $item;			
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	/**
	 * Once the new user has been activated, we must link all the awaiting invitations for this e-mail address
	 * to the newly created profile. This is a safe operation, as the user has already confirmed that he owns
	 * this mailbox by receiving the activation link.
	 * 
	 * @param UserEvent $event
	 */
	public function onUserActivated(UserEvent $event)
	{
		$stmt = $this->conn->prepare('UPDATE `'.CoreTables::INVITATION_TBL.'` SET `userId` = :id WHERE `email` = :email');
		$stmt->bindValue(':id', $event->getUser()->getId());
		$stmt->bindValue(':email', $event->getUser()->getEmail());
		$stmt->execute();
	}
	
	/**
	 * When the user is removed, clear out all the membership information.
	 * 
	 * @param UserEvent $event
	 */
	public function onUserRemoved(UserEvent $event)
	{
		$reset = array();
		foreach ($this->repositories as $repository) {
			$resetProperty = $repository->clearMembership($event->getUser());
			$reset[$resetProperty] = 0;
		}
		$this->conn->update(CoreTables::USER_TBL, $reset, ['id' => $event->getUser()->getId()]);
	}
	
	public function findAndJoin($key, User $user)
	{
		$this->transaction->requestTransaction();
		try {
			$item = Invitation::fetchByKey($this->conn, $key);
			if (empty($item)) {
				throw new ItemNotFoundException('InvitationNotFoundText', $key);
			}
			$item->join($this->conn, $user);
		} catch (Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	
	public function accept($id, User $user)
	{
		$this->transaction->requestTransaction();
		try {
			$item = Invitation::fetchByUser($this->conn, $id, $user);
			if (empty($item)) {
				throw new ItemNotFoundException('The specified invitation cannot be found.');
			}
			$resourceType = $item->getPlace()->getType();

			if (!isset($this->repositories[$resourceType])) {
				throw new ItemNotFoundException('Unknown resource type: '.$resourceType);
			}
			$this->repositories[$resourceType]->acceptInvitation($item);
			$item->remove($this->conn);
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function revoke($id, User $user)
	{
		$this->transaction->requestTransaction();
		try {
			$item = Invitation::fetchByUser($this->conn, $id, $user);
			if (empty($item)) {
				throw new ItemNotFoundException('The specified invitation cannot be found.');
			}
			$item->remove($this->conn);
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
}
