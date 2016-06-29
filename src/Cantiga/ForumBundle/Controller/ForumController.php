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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/project/{slug}/forums")
 * @Security("has_role('ROLE_PROJECT_AWARE')")
 */
class ForumController extends ProjectPageController
{
	const TEMPLATE_LOCATION = 'CantigaForumBundle:Forum:';
	
	/**
	 * @Route("/index", name="project_forum_index")
	 */
	public function indexAction(Request $request)
	{
		return $this->render(self::TEMPLATE_LOCATION . 'index.html.twig');
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
