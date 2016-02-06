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
namespace WIO\EdkBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\PublicPageController;
use Cantiga\CoreBundle\Entity\Project;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Adds some basic services for the publicly available EWC pages, such as
 * project detection.
 *
 * @author Tomasz Jędrzejewski
 */
abstract class PublicEdkController extends PublicPageController
{
	protected $project;
	private $slug;
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->slug = $request->get('slug');
		if (empty($this->slug)) {
			return $this->redirect($this->generateUrl('public_edk_error'));
		}
		
		$this->project = Project::fetchBySlug($this->get('database_connection'), $this->slug);
		if (false === $this->project) {
			return $this->redirect($this->generateUrl('public_edk_error'));
		}
		
		$projectSettings = $this->getProjectSettings();
		$projectSettings->setProject($this->project);
	}
	
	public final function getSlug()
	{
		return $this->slug;
	}
}
