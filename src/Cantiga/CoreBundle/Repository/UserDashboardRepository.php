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

use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\UserBundle\UserTables;
use Doctrine\DBAL\Connection;

class UserDashboardRepository
{
	/**
	 * @var Connection 
	 */
	private $conn;
	
	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}
	
	public function getUserMembership(CantigaUserRefInterface $user)
	{
		return $this->conn->fetchAll('SELECT p.`id`, p.`name`, p.`slug`, p.`type` '
			. 'FROM `'.CoreTables::PLACE_TBL.'` p '
			. 'INNER JOIN `'.UserTables::PLACE_MEMBERS_TBL.'` m ON m.`placeId` = p.`id` '
			. 'WHERE m.`userId` = :userId AND p.archived = 0 '
			. 'ORDER BY p.`type` DESC, p.`name`',
			[':userId' => $user->getId()]);
	}
	
	public function getArchivedUserMembership(CantigaUserRefInterface $user)
	{
		return $this->conn->fetchAll('SELECT p.`id`, p.`name`, p.`slug`, p.`type` '
			. 'FROM `'.CoreTables::PLACE_TBL.'` p '
			. 'INNER JOIN `'.UserTables::PLACE_MEMBERS_TBL.'` m ON m.`placeId` = p.`id` '
			. 'WHERE m.`userId` = :userId AND p.archived = 1 '
			. 'ORDER BY p.`type` DESC, p.`name`',
			[':userId' => $user->getId()]);
	}
	
	public function getOpenAreaRegistrations()
	{
		return $this->conn->fetchAll('SELECT `id`, `name` FROM `'.CoreTables::PROJECT_TBL.'` WHERE `areaRegistrationAllowed` = 1 AND `archived` = 0 AND `areasAllowed` = 1 ORDER BY `name`');
	}
	
	public function getInvitations(CantigaUserRefInterface $user)
	{
		return $this->conn->fetchAll('SELECT i.`id`, p.`type`, p.`name` '
			. 'FROM `'.CoreTables::INVITATION_TBL.'` i '
			. 'INNER JOIN `'.CoreTables::PLACE_TBL.'` p ON p.`id` = i.`placeId` '
			. 'WHERE `userId` = :userId ORDER BY p.`type`, p.`name`', [':userId' => $user->getId()]);
	}
	
	public function getUserAreaRequests(CantigaUserRefInterface $user)
	{
		return $this->conn->fetchAll('SELECT r.`id`, r.`name`, p.`name` '
			. 'FROM `'.CoreTables::AREA_REQUEST_TBL.'` r '
			. 'INNER JOIN `'.CoreTables::PROJECT_TBL.'` p ON p.`id` = r.`projectId` '
			. 'WHERE r.`requestorId` = :userId AND r.`status` NOT IN (2, 3) ORDER BY p.`name`, r.`name`', [':userId' => $user->getId()]);
	}
}
