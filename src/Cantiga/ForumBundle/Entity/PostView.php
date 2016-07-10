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

class PostView
{
	private $id;
	private $author;
	private $authorIp;
	private $content;
	private $order;
	
	public function __construct(array $data)
	{
		$this->id = $data['id'];
		$this->author = new AuthorSummaryView($data['authorId'], $data['authorName'], $data['createdAt'], $data['authorAvatar']);
		$this->authorIp = long2ip($data['authorIp']);
		$this->content = $data['content'];
		$this->order = $data['postOrder'];
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getAuthor()
	{
		return $this->author;
	}

	public function getAuthorIp()
	{
		return $this->authorIp;
	}
	
	public function getContent()
	{
		return $this->content;
	}

	public function getOrder()
	{
		return $this->order;
	}
}
