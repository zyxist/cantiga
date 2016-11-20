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
namespace Cantiga\CoreBundle\Api\Actions;

use Cantiga\CoreBundle\Api\BreadcrumbItem;
use Cantiga\CoreBundle\Api\Controller\RedirectHandlingInterface;

/**
 * @author Tomasz Jędrzejewski
 */
abstract class AbstractAction
{
	protected $info;
	protected $slug;
	protected $breadcrumbs;
	protected $vars;
	
	/**
	 * Creates an additional variable passed to the template.
	 * 
	 * @param string $name Variable name
	 * @param mixed $value
	 * @return \Cantiga\CoreBundle\Api\Actions\AbstractAction
	 */
	public function set($name, $value)
	{
		$this->vars[$name] = $value;
		return $this;
	}
	
	public function breadcrumbs(BreadcrumbItem $item)
	{
		$this->breadcrumbs = $item;
		return $this;
	}
	
	public function slug($slug)
	{
		$this->slug = $slug;
		return $this;
	}
	
	protected function hasBreadcrumbs()
	{
		return $this->breadcrumbs !== null;
	}

	protected function onError($controller, $message)
	{
		if ($controller instanceof RedirectHandlingInterface) {
			return $controller->onError($message);
		}
		$controller->get('session')->getFlashBag()->add('error', $message);
		return $controller->redirect($controller->generateUrl($this->info->getIndexPage(), $this->slugify()));
	}

	protected function onSuccess($controller, $message)
	{
		if ($controller instanceof RedirectHandlingInterface) {
			return $controller->onSuccess($message);
		}
		$controller->get('session')->getFlashBag()->add('info', $message);
		return $controller->redirect($controller->generateUrl($this->info->getIndexPage(), $this->slugify()));
	}

	protected function toIndexPage($controller)
	{
		if ($controller instanceof RedirectHandlingInterface) {
			return $controller->toIndexPage();
		}
		return $controller->redirect($controller->generateUrl($this->info->getIndexPage(), $this->slugify()));
	}
	
	protected function slugify($args = [])
	{
		if (!empty($this->slug)) {
			$args['slug'] = $this->slug;
		}
		return $args;
	}
	
	/**
	 * @return array
	 */
	protected final function getVars()
	{
		return $this->vars;
	}
}
