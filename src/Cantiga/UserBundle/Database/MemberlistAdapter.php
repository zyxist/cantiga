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
namespace Cantiga\UserBundle\Database;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\UserBundle\UserTables;
use Doctrine\DBAL\Connection;
use PDO;

/**
 * A couple of SQL queries hidden behind methods to mock them in the unit tests.
 */
class MemberlistAdapter
{
	private $conn;
	
	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}
	
	public function getConnection(): Connection
	{
		return $this->conn;
	}
	
	public function getUserProfile(int $userId, int $projectPlaceId)
	{
		return $this->conn->fetchAssoc('SELECT i.`id`, i.`name`, i.`avatar`, i.`lastVisit`, p.`location`, c.`email` AS `contactMail`, '
			. 'c.`telephone` AS `contactTelephone`, c.`notes` AS `notes` '
			. 'FROM `'.CoreTables::USER_TBL.'` i '
			. 'INNER JOIN `'.CoreTables::USER_PROFILE_TBL.'` p ON p.`userId` = i.`id` '
			. 'LEFT JOIN `'.CoreTables::CONTACT_TBL.'` c ON c.`userId` = i.`id` AND c.`placeId` = :projectPlaceId '
			. 'WHERE i.`active` = 1 AND i.`removed` = 0 AND i.`id` = :userId', [':userId' => $userId, ':projectPlaceId' => $projectPlaceId]);
	}
	
	public function findUserPlaces(int $userId, int $projectPlaceId): array
	{
		return $this->conn->fetchAll('SELECT p.*, m.* '
			. 'FROM `'.CoreTables::PLACE_TBL.'` p '
			. 'INNER JOIN `'.UserTables::PLACE_MEMBERS_TBL.'` m ON m.`placeId` = p.`id` '
			. 'WHERE m.`userId` = :userId AND (p.`rootPlaceId` = :projectPlaceId OR p.`id` = :projectPlaceId)', [':userId' => $userId, ':projectPlaceId' => $projectPlaceId]);
	}
	
	/**
	 * This method is needed for {@link RightVoter} which currently has no other way to determine the relationship between
	 * the group and associated areas. In the future, with more flexible hierarchy model, such a direct check may be removed.
	 * 
	 * @param int $groupId Group ID
	 * @return array
	 */
	public function findAreaPlaceIds(int $groupId): array
	{
		$stmt = $this->conn->prepare('SELECT `placeId` FROM `'.CoreTables::AREA_TBL.'` WHERE `groupId` = :groupId');
		$stmt->bindValue(':groupId', $groupId);
		$stmt->execute();
		$result = [];
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$result[] = $row[0];
		}
		$stmt->closeCursor();
		return $result;
	}
}
