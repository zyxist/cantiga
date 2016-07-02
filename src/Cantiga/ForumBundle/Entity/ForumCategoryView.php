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

class ForumCategoryView implements ForumParentInterface, ForumContainerInterface
{
	private $id;
	private $name;
	private $root;
	private $forums = [];
	
	public function __construct(ForumRoot $root, array $categoryData)
	{
		$this->root = $root;
		$this->id = $categoryData['id'];
		$this->name = $categoryData['name'];
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getRoot()
	{
		return $this->root;
	}
	
	public function appendForum(ForumView $view)
	{
		$this->forums[] = $view;
	}

	public function getForums()
	{
		return $this->forums;
	}

	public function isVisible()
	{
		return sizeof($this->forums) > 0;
	}

	public function getParent()
	{
		return null;
	}
	
	public function isLinkable()
	{
		return false;
	}
	
	public function sumTopics() {
		$sum = 0;
		foreach ($this->forums as $forum) {
			$sum += $forum->getTopicNum();
		}
		return $sum;
	}
	
	public function sumPosts() {
		$sum = 0;
		foreach ($this->forums as $forum) {
			$sum += $forum->getPostNum();
		}
		return $sum;
	}

}
