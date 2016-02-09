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
namespace WIO\EdkBundle\Repository;

use Cantiga\Metamodel\Capabilities\InsertableRepositoryInterface;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use WIO\EdkBundle\EdkEvents;
use WIO\EdkBundle\EdkTables;
use WIO\EdkBundle\Entity\EdkParticipant;
use WIO\EdkBundle\Entity\EdkRegistrationSettings;
use WIO\EdkBundle\Event\RegistrationEvent;

/**
 * Manages the participants.
 *
 * @author Tomasz Jędrzejewski
 */
class EdkParticipantRepository implements InsertableRepositoryInterface
{
	const AVAILABILITY_TIME = 300;
	
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
	
	public function __construct(Connection $conn, Transaction $transaction, EventDispatcherInterface $eventDispatcher)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->eventDispatcher = $eventDispatcher;
	}
	
	public function getPublicRegistration($routeId, $expectedAreaStatus)
	{
		$this->transaction->requestTransaction();
		try {
			$item = EdkRegistrationSettings::fetchPublic($this->conn, $routeId, $expectedAreaStatus);
			if (false === $item) {
				throw new ItemNotFoundException('There is no registration for this route available.');
			}
			return $item;
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function register($entity, $ipAddr, $slug)
	{
		$this->transaction->requestTransaction();
		try {
			$previous = $this->conn->fetchAssoc('SELECT `id` FROM `'.EdkTables::PARTICIPANT_TBL.'` WHERE `ipAddress` = :ip AND `createdAt` > :time', [':ip' => ip2long($ipAddr), ':time' => time() - self::AVAILABILITY_TIME]);
			
			if (!empty($previous)) {
				throw new ModelException('You have already registered not so long ago from this computer. Please wait a few moments.');
			}
				
			$id = $entity->insert($this->conn);			
			$this->eventDispatcher->dispatch(EdkEvents::REGISTRATION_COMPLETED, new RegistrationEvent($entity, $slug));
			return $id;
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function getItemByKey($accessKey, $expectedAreaStatus)
	{
		$this->transaction->requestTransaction();
		try {
			$item = EdkParticipant::fetchByKey($this->conn, $accessKey, $expectedAreaStatus, false);
			if (false === $item) {
				throw new ItemNotFoundException('Participant not found.');
			}
			return $item;
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function removeItemByKey($accessKey, $expectedAreaStatus)
	{
		$this->transaction->requestTransaction();
		try {
			$item = EdkParticipant::fetchByKey($this->conn, $accessKey, $expectedAreaStatus, true);
			if (false === $item) {
				throw new ItemNotFoundException('Participant not found.');
			}
			$item->remove($this->conn);		
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}

	public function insert($entity)
	{
		$this->transaction->requestTransaction();
		try {
			return $entity->insert($this->conn);
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
}
