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
namespace Cantiga\DiscussionBundle\Database;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\DiscussionBundle\DiscussionTables;
use Doctrine\DBAL\Connection;

class DiscussionAdapter
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

	public function findVisibleChannels($projectId, array $entityIds, $visibilityUnit, $maxLevel)
	{
		return $this->conn->fetchAll('SELECT c.`id`, c.`name`, s.`lastPostTime`, c.`color`, c.`icon`, c.`enabled` '
			. 'FROM `'.DiscussionTables::DISCUSSION_CHANNEL_TBL.'` c '
			. 'LEFT JOIN `'.DiscussionTables::DISCUSSION_SUBCHANNEL_TBL.'` s ON (s.`channelId` = c.`id` AND s.`entityId` IN ('.implode(',', $entityIds).')) '
			. 'WHERE c.`projectId` = :projectId AND c.`'.$visibilityUnit.'` = 1 AND c.`discussionGrouping` <= :maxLevel '
			. 'ORDER BY `enabled` DESC, `name`', [':projectId' => $projectId, ':maxLevel' => $maxLevel]);
	}
	
	public function selectRecentPosts(int $subchannelId, int $postNumber, int $sinceTime): array
	{
		return $this->conn->fetchAll('SELECT p.`id`, p.`createdAt`, p.`content`, u.`id` AS `userId`, u.`name` AS `userName`, u.`avatar` AS `avatar` '
			. 'FROM `'.DiscussionTables::DISCUSSION_POST_TBL.'` p '
			. 'INNER JOIN `'.CoreTables::USER_TBL.'` u ON u.`id` = p.`authorId` '
			. 'WHERE p.`subchannelId` = :subchannelId AND p.`createdAt` < :sinceTime '
			. 'ORDER BY `createdAt` DESC '
			. 'LIMIT '.$postNumber, [':subchannelId' => $subchannelId, ':sinceTime' => $sinceTime]);
	}
	
	public function fetchSubchannel(int $channelId, int $entityId)
	{
		return $this->conn->fetchAssoc('SELECT * FROM `'.DiscussionTables::DISCUSSION_SUBCHANNEL_TBL.'` '
			. 'WHERE `channelId` = :channelId AND `entityId` = :entityId', [':channelId' => $channelId, ':entityId' => $entityId]);
	}
	
	public function createSubchannel(int $channelId, int $entityId): array
	{
		$values = ['channelId' => $channelId, 'entityId' => $entityId, 'lastPostTime' => null];
		$this->conn->insert(DiscussionTables::DISCUSSION_SUBCHANNEL_TBL, $values);
		$values['id'] = $this->conn->lastInsertId();
		return $values;
	}
	
	public function publishPost($data): int
	{
		$this->conn->insert(DiscussionTables::DISCUSSION_POST_TBL, $data);
		return $this->conn->lastInsertId();
	}
	
	public function updateSubchannelActivity($subchannelId, $time)
	{
		$this->conn->update(DiscussionTables::DISCUSSION_SUBCHANNEL_TBL, ['lastPostTime' => $time], ['id' => $subchannelId]);
	}
}
