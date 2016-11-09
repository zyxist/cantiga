<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Tomasz Jedrzejewski.
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
	
	public function findVisibleChannels($projectId, $visibilityUnit)
	{
		return $this->conn->fetchAll('SELECT `id`, `name`, `lastPostTime`, `color`, `icon` '
			. 'FROM `'.DiscussionTables::DISCUSSION_CHANNEL_TBL.'` '
			. 'WHERE `projectId` = :projectId AND `'.$visibilityUnit.'` = 1 '
			. 'ORDER BY `name`', [':projectId' => $projectId]);
	}
	
	public function selectRecentPostsByEntity(int $channelId, int $entityId, int $postNumber, int $sinceTime): array
	{
		return $this->conn->fetchAll('SELECT p.`id`, p.`createdAt`, p.`content`, u.`id`, u.`name`, u.`avatar` '
			. 'FROM `'.DiscussionTables::DISCUSSION_POST_TBL.'` p '
			. 'INNER JOIN `'.CoreTables::USER_TBL.'` u ON u.`id` = p.`authorId` '
			. 'WHERE p.`channelId` = :channelId AND p.`entityId` = :entityId AND p.`createdAt` < :sinceTime '
			. 'ORDER BY `createdAt` DESC '
			. 'LIMIT '.$postNumber, [':channelId' => $channelId, ':entityId' => $entityId, ':sinceTime' => $sinceTime]);
	}
	
	public function selectRecentPosts(int $channelId, int $postNumber, int $sinceTime): array
	{
		return $this->conn->fetchAll('SELECT p.`id`, p.`createdAt`, p.`content`, u.`id` AS `userId`, u.`name` AS `userName`, u.`avatar` AS `avatar` '
			. 'FROM `'.DiscussionTables::DISCUSSION_POST_TBL.'` p '
			. 'INNER JOIN `'.CoreTables::USER_TBL.'` u ON u.`id` = p.`authorId` '
			. 'WHERE p.`channelId` = :channelId AND p.`createdAt` < :sinceTime '
			. 'ORDER BY `createdAt` DESC '
			. 'LIMIT '.$postNumber, [':channelId' => $channelId, ':sinceTime' => $sinceTime]);
	}
	
	public function publishPost($data): int
	{
		$this->conn->insert(DiscussionTables::DISCUSSION_POST_TBL, $data);
		return $this->conn->lastInsertId();
	}
	
	public function updateChannelActivity($channelId, $time)
	{
		$this->conn->update(DiscussionTables::DISCUSSION_CHANNEL_TBL, ['lastPostTime' => $time], ['id' => $channelId]);
	}
}
