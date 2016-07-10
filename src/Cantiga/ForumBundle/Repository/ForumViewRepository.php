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
namespace Cantiga\ForumBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\ForumBundle\Entity\ForumCategoryView;
use Cantiga\ForumBundle\Entity\ForumParent;
use Cantiga\ForumBundle\Entity\ForumRoot;
use Cantiga\ForumBundle\Entity\ForumView;
use Cantiga\ForumBundle\Entity\PostView;
use Cantiga\ForumBundle\Entity\TopicUtils\TopicFinderInterface;
use Cantiga\ForumBundle\Entity\TopicView;
use Cantiga\ForumBundle\ForumTables;
use Doctrine\DBAL\Connection;

/**
 * Offers an access to the list of forums, categories, topics, etc...
 */
class ForumViewRepository
{
	private $conn;
	
	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}
	
	public function fetchMainPageData(ForumRoot $root)
	{
		$categoryData = $this->conn->fetchAll('SELECT * FROM `'.ForumTables::FORUM_CATEGORY_TBL.'` WHERE `rootId` = :rootId ORDER BY `displayOrder`', [':rootId' => $root->getId()]);
		$forumData = $this->conn->fetchAll('SELECT * FROM `'.ForumTables::FORUM_TBL.'` WHERE `rootId` = :rootId AND `parentId` IS NULL ORDER BY `categoryId`, `leftPosition`', [':rootId' => $root->getId()]);
	
		$categories = [];
		$categoryMap = [];
		foreach ($categoryData as $category) {
			$item = new ForumCategoryView($root, $category);
			$categories[] = $item;
			$categoryMap[$item->getId()] = $item;
		}
		foreach ($forumData as $forum) {
			$category = $categoryMap[$forum['categoryId']];
			$category->appendForum(new ForumView($root, $category, $forum));
		}
		return $categories;
	}
	
	public function fetchForumStructureFor(ForumRoot $root, $forumId)
	{
		$forumData = $this->conn->fetchAssoc('SELECT * FROM `'.ForumTables::FORUM_TBL.'` WHERE `id` = :id', [':id' => $forumId]);
		$childrenData = $this->conn->fetchAll('SELECT * FROM `'.ForumTables::FORUM_TBL.'` WHERE `parentId` = :parentId ORDER BY `leftPosition`', [':parentId' => $forumId]);		
		$currentParent = $this->fetchParents($forumId);
		$forumView = new ForumView($root, $currentParent, $forumData);
		
		foreach ($childrenData as $childData) {
			$forumView->appendForum(new ForumView($root, $forumView, $childData));
		}
		
		$this->fetchTopicsAndAnnouncements($root, $forumView);
		return $forumView;
	}
	
	public function fetchTopicWithPosts(ForumRoot $root, TopicFinderInterface $topicFinder)
	{
		$topic = $topicFinder->findTopic($this->conn);
		$topic->setParent($this->fetchParents($topic->getForumId(), true));
		$postData = $this->conn->fetchAll('SELECT p.*, u.name AS `authorName`, u.avatar AS `authorAvatar`, c.`content` '
			. 'FROM '.ForumTables::POST_TBL.' p '
			. 'INNER JOIN '.ForumTables::POST_CONTENT_TPL.' c ON c.`postId` = p.`id` '
			. 'INNER JOIN '.CoreTables::USER_TBL.' u ON u.id = p.authorId '
			. 'WHERE p.topicId = :topicId '
			. 'ORDER BY p.postOrder LIMIT 10', [':topicId' => $topic->getId()]);
		foreach ($postData as $postItem) {
			$topic->appendPost(new PostView($postItem));
		}
		return $topic;
	}
	
	private function fetchParents($forumId, $inclusive = false)
	{
		$leftOperator = $inclusive ? '<=' : '<';
		$rightOperator = $inclusive ? '>=' : '>';
		$parentData = $this->conn->fetchAll('SELECT f1.`id`, f1.`name`, f1.`categoryId` FROM `'.ForumTables::FORUM_TBL.'` f1 '
			. 'INNER JOIN `'.ForumTables::FORUM_TBL.'` f2 ON f1.`leftPosition` '.$leftOperator.' f2.`leftPosition` AND f1.`rightPosition` '.$rightOperator.' f2.`rightPosition` '
			. 'WHERE f2.`id` = :id '
			. 'ORDER BY f1.`leftPosition`', [':id' => $forumId]);
		$categoryData = $this->conn->fetchAssoc('SELECT * FROM `'.ForumTables::FORUM_CATEGORY_TBL.'` WHERE `id` = :id', [':id' => current($parentData)['categoryId']]);
		$category = new ForumParent($categoryData['id'], $categoryData['name'], false, null);
		$currentParent = $category;
		foreach ($parentData as $p) {
			$currentParent = new ForumParent($p['id'], $p['name'], true, $currentParent);
		}
		return $currentParent;
	}
	
	private function fetchTopicsAndAnnouncements(ForumRoot $root, ForumView $populatedForum)
	{
		$announcementData = $this->conn->fetchAll('SELECT * FROM `'.ForumTables::TOPIC_TBL.'` WHERE `rootId` = :rootId AND `type` = '.TopicView::TYPE_ANNOUNCEMENT.' ORDER BY `lastPostCreatedAt`', [
				':rootId' => $root->getId()
			]);
		$topicData = $this->conn->fetchAll('SELECT * FROM `'.ForumTables::TOPIC_TBL.'` WHERE `forumId` = :forumId AND `type` <> '.TopicView::TYPE_ANNOUNCEMENT.' ORDER BY `lastPostCreatedAt` LIMIT 50', [
				':forumId' => $populatedForum->getId()
			]);
		
		foreach ($announcementData as $announcement) {
			$populatedForum->appendAnnouncement(new TopicView($announcement));
		}
		foreach ($topicData as $topic) {
			$populatedForum->appendTopic(new TopicView($topic));
		}
	}
}
