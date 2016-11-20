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
namespace Cantiga\Metamodel;

use Doctrine\DBAL\Connection;

/**
 * In PHP, the flow is very simple. We prepare to run the action, we run the action, we finalize.
 * Advanced transactional management, known i.e. from Java is too complex (I know, what I'm saying,
 * because I'm a professional Java programmer). Here the entire action can run within a single
 * transaction that is rolled back if something goes wrong.
 * 
 * <p>The main use case is situation, where we call a nested block of code that also requests a
 * transaction. The service does nothing, if the transaction is already opened, and finalizes it
 * at the very end.
 *
 * @author Tomasz Jędrzejewski
 */
class Transaction
{
	/**
	 * @var Connection
	 */
	private $conn;
	private $inTransaction = false;
	private $shouldCommit = true;
	
	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}
	
	/**
	 * Starts a new transactional block, if none is open, or silently completes, if the transaction
	 * is already opened.
	 */
	public function requestTransaction()
	{
		if (!$this->inTransaction) {
			$this->conn->beginTransaction();
			$this->inTransaction = true;
		}
	}
	
	/**
	 * Requests that the transaction should be rolled back at the end of the action. The transaction
	 * is not physically closed here.
	 */
	public function requestRollback()
	{
		if ($this->inTransaction) {
			$this->shouldCommit = false;
		}
	}
	
	/**
	 * Closes the transaction. If the transaction has been marked to rollback, it is rolled back. Otherwise
	 * it is committed. The method does nothing, if the transaction is not open.
	 */
	public function closeTransaction()
	{
		if ($this->inTransaction) {
			$this->inTransaction = false;
			if($this->shouldCommit) {
				$this->conn->commit();
			} else {
				$this->conn->rollBack();
			}
			$this->shouldCommit = true;
		}
	}
}
