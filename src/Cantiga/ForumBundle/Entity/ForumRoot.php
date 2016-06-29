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

use Cantiga\CoreBundle\Entity\Project;
use Cantiga\ForumBundle\ForumTables;
use Doctrine\DBAL\Connection;

/**
 * Experiment with loose coupling between projects and discussion forum.
 * In the forum bounded domain, the forum has a root which currently is 
 * an equivalent to a project, but may be extended in the future to something
 * else.
 */
class ForumRoot
{
	private $id;
	private $name;
	
	public static function fromProject(Project $project)
	{
		$root = new ForumRoot();
		$root->id = $project->getId();
		$root->name = $project->getName();
		return $root;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function save(Connection $conn)
	{
		$exists = $conn->fetchColumn('SELECT `id` FROM `'.ForumTables::FORUM_ROOT_TBL.'` WHERE `id` = :id', [':id' => $this->id]);
		if (!empty($exists)) {
			$conn->insert(ForumTables::FORUM_ROOT_TBL, ['id' => $id, 'name' => $name]);
		} else {
			$conn->update(ForumTables::FORUM_ROOT_TBL, ['id' => $id, 'name' => $name]);
		}
	}
}
