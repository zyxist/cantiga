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

class TopicView
{
	const TYPE_ANNOUNCEMENT = 2;
	const TYPE_PINNED = 1;
	const TYPE_NORMAL = 0;
	
	private $id;
	private $title;
	private $author;
	private $lastPoster;
	private $firstPostId;
	private $lastPostId;
	private $closed;
	private $type;
	private $replyNum;
	private $viewNum;
	
	public function __construct(array $data)
	{
		$this->id = $data['id'];
		$this->title = $data['title'];
		$this->author = new AuthorSummaryView($data['authorId'], $data['authorName'], $data['createdAt']);
		$this->lastPoster = new AuthorSummaryView($data['lastAuthorId'], $data['lastAuthorName'], $data['lastPostCreatedAt']);
		$this->firstPostId = $data['firstPostId'];
		$this->lastPostId = $data['lastPostId'];
		$this->type = $data['type'];
		$this->closed = $data['closed'];
		$this->replyNum = $data['replyNum'];
		$this->viewNum = $data['viewNum'];
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getAuthor()
	{
		return $this->author;
	}

	public function getLastPoster()
	{
		return $this->lastPoster;
	}

	public function getFirstPostId()
	{
		return $this->firstPostId;
	}

	public function getLastPostId()
	{
		return $this->lastPostId;
	}
	
	public function getType()
	{
		return $this->type;
	}

	public function isClosed()
	{
		return $this->closed;
	}
	
	public function getReplyNum()
	{
		return $this->replyNum;
	}

	public function getViewNum()
	{
		return $this->viewNum;
	}
}
