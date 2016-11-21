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
		return $this->conn->fetchAll(
			'(SELECT p.`id`, p.`name`, p.`slug`, \'Project\' AS `type` FROM `'.CoreTables::PROJECT_TBL.'` p INNER JOIN `'.CoreTables::PROJECT_MEMBER_TBL.'` m ON m.`projectId` = p.`id` WHERE m.`userId` = :userId1) '.
			'UNION (SELECT g.`id`, g.`name`, g.`slug`, \'Group\' AS `type` FROM `'.CoreTables::GROUP_TBL.'` g INNER JOIN `'.CoreTables::GROUP_MEMBER_TBL.'` m ON m.`groupId` = g.`id` WHERE m.`userId` = :userId2) '.
			'UNION (SELECT a.`id`, a.`name`, a.`slug`, \'Area\' AS `type` FROM `'.CoreTables::AREA_TBL.'` a INNER JOIN `'.CoreTables::AREA_MEMBER_TBL.'` m ON m.`areaId` = a.`id` WHERE m.`userId` = :userId3) '.
			'ORDER BY `type` DESC, `name`',
			[':userId1' => $user->getId(), ':userId2' => $user->getId(), ':userId3' => $user->getId()]);
	}
	
	public function getOpenAreaRegistrations()
	{
		return $this->conn->fetchAll('SELECT `id`, `name` FROM `'.CoreTables::PROJECT_TBL.'` WHERE `areaRegistrationAllowed` = 1 AND `archived` = 0 AND `areasAllowed` = 1 ORDER BY `name`');
	}
	
	public function getInvitations(CantigaUserRefInterface $user)
	{
		return $this->conn->fetchAll('SELECT `id`, `resourceType`, `resourceName` '
			. 'FROM `'.CoreTables::INVITATION_TBL.'` '
			. 'WHERE `userId` = :userId ORDER BY `resourceType`, `resourceName`', [':userId' => $user->getId()]);
	}
	
	public function getUserAreaRequests(CantigaUserRefInterface $user)
	{
		return $this->conn->fetchAll('SELECT r.`id`, r.`name`, p.`name` '
			. 'FROM `'.CoreTables::AREA_REQUEST_TBL.'` r '
			. 'INNER JOIN `'.CoreTables::PROJECT_TBL.'` p ON p.`id` = r.`projectId` '
			. 'WHERE r.`requestorId` = :userId AND r.`status` NOT IN (2, 3) ORDER BY p.`name`, r.`name`', [':userId' => $user->getId()]);
	}
}
