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
namespace Cantiga\ForumBundle\Entity;

use Cantiga\Metamodel\DataMappers;

class ForumView implements ForumContainerInterface, ForumParentInterface
{
	private $id;
	private $name;
	private $parent;
	private $description;
	private $topicNum;
	private $postNum;
	private $forums;
	
	private $announcements = [];
	private $topics = [];
	
	public function __construct(ForumRoot $root, ForumParentInterface $parent, array $data)
	{
		DataMappers::fromArray($this, $data);
		$this->parent = $parent;
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getTopicNum()
	{
		return $this->topicNum;
	}

	public function getPostNum()
	{
		return $this->postNum;
	}
	
	public function isLinkable()
	{
		return true;
	}

	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	public function setTopicNum($topicNum)
	{
		$this->topicNum = $topicNum;
		return $this;
	}

	public function setPostNum($postNum)
	{
		$this->postNum = $postNum;
		return $this;
	}
	
	public function getParent()
	{
		return $this->parent;
	}
	
	public function appendForum(ForumView $view)
	{
		$this->forums[] = $view;
	}

	public function getForums()
	{
		return $this->forums;
	}
	
	public function fetchTopDownParents()
	{
		$parent = $this;
		$list = [];
		while (null != $parent) {
			$list[] = $parent;
			$parent = $parent->getParent();
		}
		$list = array_reverse($list);
		return $list;
	}
	
	public function appendAnnouncement(TopicView $announcement)
	{
		$this->announcements[] = $announcement;
	}
	
	public function getAnnouncements()
	{
		return $this->announcements;
	}
	
	public function appendTopic(TopicView $topic)
	{
		$this->topics[] = $topic;
	}
	
	public function getTopics()
	{
		return $this->topics;
	}
}
