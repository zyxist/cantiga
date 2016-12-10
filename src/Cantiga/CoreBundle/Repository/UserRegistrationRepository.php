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
namespace Cantiga\CoreBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Language;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\CoreBundle\Entity\UserRegistration;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\CoreBundle\Event\UserEvent;
use Cantiga\CoreBundle\Event\UserRegistrationEvent;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Form\EntityTransformerInterface;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserRegistrationRepository implements EntityTransformerInterface
{
	const PRUNE_PERIOD = 2592000; // 30 days
	
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
	 * @var TimeFormatterInterface
	 */
	private $timeFormatter;
	private $encoderFactory;
	
	public function __construct(Connection $conn, Transaction $transaction, EventDispatcherInterface $eventDispatcher, $encoderFactory, TimeFormatterInterface $timeFormatter)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->eventDispatcher = $eventDispatcher;
		$this->encoderFactory = $encoderFactory;
		$this->timeFormatter = $timeFormatter;
	}
	
	/**
	 * @return DataTable
	 */
	public function createDataTable()
	{
		$dt = new DataTable();
		$dt->id('id', 'i.id')
			->searchableColumn('name', 'i.name')
			->searchableColumn('login', 'i.login')
			->column('requestTime', 'i.requestTime')
			->column('requestIp', 'i.requestIp');
		return $dt;
	}
	
	public function listData(DataTable $dataTable)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.name', 'name')
			->field('i.login', 'login')
			->field('i.requestTime', 'requestTime')
			->field('i.requestIp', 'requestIp')
			->from(CoreTables::USER_REGISTRATION_TBL, 'i');	
		
		$recordsTotal = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(id)', 'cnt')
			->where($dataTable->buildCountingCondition($qb->getWhere()))
			->fetchCell($this->conn);
		$recordsFiltered = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(id)', 'cnt')
			->where($dataTable->buildFetchingCondition($qb->getWhere()))
			->fetchCell($this->conn);
		
		$qb->postprocess(function(array $row) { 
			$row['requestTimeFormatted'] = $this->timeFormatter->ago($row['requestTime']);
			$row['requestIpFormatted'] = long2ip($row['requestIp']);
			return $row;
		});

		$dataTable->processQuery($qb);
		return $dataTable->createAnswer(
			$recordsTotal,
			$recordsFiltered,
			$qb->where($dataTable->buildFetchingCondition($qb->getWhere()))->fetchAll($this->conn)
		);
	}
	
	/**
	 * @return UserRegistration
	 */
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		$data = $this->conn->fetchAssoc('SELECT r.*, l.`id` AS `language_id`, l.`name` AS `language_name`, l.`locale` AS `language_locale` '
			. 'FROM `'.CoreTables::USER_REGISTRATION_TBL.'` r '
			. 'INNER JOIN `'.CoreTables::LANGUAGE_TBL.'` l ON l.`id` = r.`languageId` '
			. 'WHERE r.`id` = :id', [':id' => $id]);
		
		if(false === $data) {
			$this->transaction->requestRollback();
			throw new ItemNotFoundException('The specified item has not been found.', $id);
		}
		$item = UserRegistration::fromArray($data);
		$item->setLanguage(Language::fromArray($data, 'language'));
		return $item;
	}
	
	public function register(UserRegistration $item)
	{
		$this->transaction->requestTransaction();
		try {
			$item->getPasswordBuilder()->processInitialPassword($this->encoderFactory->getEncoder(new User()));
			$item->insert($this->conn);
			$this->eventDispatcher->dispatch(CantigaEvents::USER_REGISTRATION, new UserRegistrationEvent($item));
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function activate($id, $provisionKey)
	{
		try {
			$item = $this->getItem($id);
			$user = $item->activate($provisionKey, $this->timeFormatter->getTimezone()->getName());
			$user->insert($this->conn);
			$item->remove($this->conn);
			$this->eventDispatcher->dispatch(CantigaEvents::USER_ACTIVATED, new UserEvent($user));
		} catch(\Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function remove(UserRegistration $item)
	{
		$this->transaction->requestTransaction();
		try {
			$item->remove($this->conn);
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function pruneOld()
	{
		$this->transaction->requestTransaction();
		try {
			$stmt = $this->conn->executeQuery('DELETE FROM `'.CoreTables::USER_REGISTRATION_TBL.'` WHERE `requestTime` < :time', [':time' => time() - self::PRUNE_PERIOD]);
			return $stmt->rowCount();
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function getFormChoices()
	{
		$this->transaction->requestTransaction();
		$stmt = $this->conn->query('SELECT `id`, `name` FROM `'.CoreTables::USER_REGISTRATION_TBL.'` ORDER BY `name`');
		$result = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$result[$row['id']] = $row['name'];
		}
		$stmt->closeCursor();
		return $result;
	}

	public function transformToEntity($key)
	{
		return $this->getItem($key);
	}

	public function transformToKey($entity)
	{
		return $entity->getId();
	}
}