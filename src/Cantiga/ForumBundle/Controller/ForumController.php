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

namespace Cantiga\ForumBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\ForumBundle\Entity\ForumRoot;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/project/{slug}/forums")
 * @Security("has_role('ROLE_PROJECT_AWARE')")
 */
class ForumController extends ProjectPageController
{
	const VIEW_REPOSITORY = 'cantiga.forum.repo.forum_view';
	const TEMPLATE_LOCATION = 'CantigaForumBundle:Forum:';
	
	/**
	 * @Route("/index", name="project_forum_index")
	 */
	public function indexAction(Request $request)
	{
		$this->breadcrumbs()->entryLink('Forums', 'project_forum_index', ['slug' => $this->getSlug()]);
		
		$svc = $this->get(self::VIEW_REPOSITORY);
		$categories = $svc->fetchMainPageData(ForumRoot::fromProject($this->getActiveProject()));
		
		$totalPosts = 0;
		$totalTopics = 0;
		foreach ($categories as $category) {
			$totalPosts += $category->sumPosts();
			$totalTopics += $category->sumTopics();
		}
		
		return $this->render(self::TEMPLATE_LOCATION . 'index.html.twig', [
			'categories' => $categories,
			'totalTopics' => $totalTopics,
			'totalPosts' => $totalPosts]);
	}
	
	/**
	 * @Route("/viewforum", name="project_forum_viewforum")
	 */
	public function viewForumAction(Request $request)
	{
		return $this->render(self::TEMPLATE_LOCATION . 'viewforum.html.twig');
	}
	
	/**
	 * @Route("/viewtopic", name="project_forum_viewtopic")
	 */
	public function viewTopicAction(Request $request)
	{
		return $this->render(self::TEMPLATE_LOCATION . 'viewtopic.html.twig', ['user' => $this->getUser() ]);
	}
}
