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
namespace Cantiga\DiscussionBundle\Entity;

use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\CoreBundle\Entity\Place;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\DiscussionBundle\Database\DiscussionAdapter;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\DataMappers;

/**
 * Subchannels allow for independent discussions within groups and areas on the same channel. Each group/area
 * has its own discussion invisible for the others. The subchannel is created automatically, when the user from the
 * given group/area enters the channel for the first time. The rules for creating subchannels depend on the channel
 * settings. Especially, there can be only one "global" subchannel visible for everyone within the project.
 */
class Subchannel implements IdentifiableInterface
{
	private $id;
	/**
	 * @var Channel
	 */
	private $channel;
	private $entity;
	private $lastPostTime;
	
	public static function lazilyFetchByChannel(DiscussionAdapter $adapter, Channel $channel, HierarchicalInterface $entity)
	{
		$normalizedEntity = self::chooseEntity($channel->getDiscussionGrouping(), $entity);
		if (null !== $normalizedEntity) {
			$data = $adapter->fetchSubchannel($channel->getId(), $normalizedEntity->getId());
			if (empty($data)) {
				$data = $adapter->createSubchannel($channel->getId(), $normalizedEntity->getId());
			}
			$subchannel = self::fromArray($data);
			$subchannel->setChannel($channel);
			$subchannel->setEntity($normalizedEntity);
			return $subchannel;
		}
		return null;
	}
	
	public static function chooseEntity(int $discussionGrouping, HierarchicalInterface $entity)
	{
		switch ($discussionGrouping) {
			case 0:
				$normalizedEntity = $entity->getElementOfType(HierarchicalInterface::TYPE_PROJECT);
				break;
			case 1:
				$normalizedEntity = $entity->getElementOfType(HierarchicalInterface::TYPE_GROUP);
				break;
			case 2:
				$normalizedEntity = $entity->getElementOfType(HierarchicalInterface::TYPE_AREA);
				break;
		}
		if (!empty($normalizedEntity)) {
			return $normalizedEntity->getPlace();
		}
		return null;
	}
	
	public static function fromArray($array, $prefix = ''): Subchannel
	{
		$item = new Subchannel;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getChannel(): Channel
	{
		return $this->channel;
	}

	public function getEntity(): Place
	{
		return $this->entity;
	}

	public function getLastPostTime()
	{
		return $this->lastPostTime;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function setChannel(Channel $channel)
	{
		$this->channel = $channel;
	}

	function setEntity(Place $entity)
	{
		$this->entity = $entity;
	}

	function setLastPostTime($lastPostTime)
	{
		$this->lastPostTime = $lastPostTime;
	}
	
	public function publish(DiscussionAdapter $adapter, $content, User $user, HierarchicalInterface $context)
	{
		if ($this->channel->canPost($context)) {
			$time = time();
			$adapter->publishPost([
				'subchannelId' => $this->getId(),
				'authorId' => $user->getId(),
				'content' => $content,
				'createdAt' => $time
			]);
			$adapter->updateSubchannelActivity($this->getId(), $time);
			return true;
		}
		return false;
	}
	
	public function getRecentPostsSince(DiscussionAdapter $adapter, int $postNumber, int $sinceTime): array
	{
		return $this->groupByDay($adapter->selectRecentPosts($this->getId(), $postNumber, $sinceTime));
	}
	
	private function groupByDay(array $recentPosts) {
		$results = [];
		$i = 0;
		foreach ($recentPosts as $postInfo) {
			$currentDay = floor($postInfo['createdAt'] / 86400);
			if (sizeof($results) > 0 && $currentDay != $results[$i]['day']) {
				$i++;
			}
			if (!isset($results[$i])) {
				$results[$i] = [
					'time' => $postInfo['createdAt'],
					'day' => $currentDay,
					'posts' => []
				];
			}
			$results[$i]['posts'][] = $postInfo;
		}
		return $results;
	}
}
