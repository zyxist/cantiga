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
namespace Cantiga\CoreBundle\Api;

use Symfony\Component\Routing\RouterInterface;

/**
 * Produces the breadcrumbs navigation bar.
 *
 * @author Tomasz Jędrzejewski
 */
class Breadcrumbs
{
	const STATIC_ITEM = 0;
	const CLICKABLE = 1;
	const WORKGROUP = 2;
	
	private $translator;
	/**
	 * @var RouterInterface 
	 */
	private $router;
	private $links = [];
	private $workgroup;
	private $entryPage;
	
	public function __construct($translator, RouterInterface $router)
	{
		$this->translator = $translator;
		$this->router = $router;
	}
	
	public function workgroup($workgroupName)
	{
		$this->links[] = ['type' => self::WORKGROUP, 'wg' => $workgroupName];
		$this->workgroup = $workgroupName;
		return $this;
	}
	
	public function staticItem($text)
	{
		$this->links[] = ['type' => self::STATIC_ITEM, 'text' => $text];
		return $this;
	}
	
	/**
	 * Not only configures the new element of the breadcrumb, but also sets the given route
	 * as active option in the menu.
	 * 
	 * @param string $text
	 * @param string $route
	 * @param array $args
	 * @return Cantiga\CoreBundle\Api\Breadcrumbs
	 */
	public function entryLink($text, $route, array $args = [])
	{
		$this->entryPage = $route;
		$this->link($text, $route, $args);
		return $this;
	}
	
	public function link($text, $route, array $args = [])
	{
		$this->links[] = ['type' => self::CLICKABLE, 'text' => $text, 'route' => $route, 'args' => $args];
		return $this;
	}
	
	public function url($text, $url)
	{
		$this->links[] = ['type' => self::CLICKABLE, 'text' => $text, 'url' => $url];
		return $this;
	}
	
	public function item(BreadcrumbItem $item)
	{
		if($item->isEntry()) {
			$this->entryPage = $item->getRoute();
		}
		$this->link($item->getText(), $item->getRoute(), $item->getArgs());
		return $this;
	}
	
	public function getWorkgroup()
	{
		return $this->workgroup;
	}
	
	public function getEntryPage()
	{
		return $this->entryPage;
	}
	
	public function fetch(Workspace $workspace)
	{
		$bc = array();
		foreach ($this->links as $link) {
			switch($link['type']) {
				case self::STATIC_ITEM:
					$bc[] = ['text' => $link['text'], 'link' => ''];
					break;
				case self::CLICKABLE:
					if (isset($link['url'])) {
						$bc[] = ['text' => $link['text'], 'link' => $link['url']];
					} else {
						$bc[] = ['text' => $link['text'], 'link' => $this->router->generate($link['route'], $link['args'])];
					}
					break;
				case self::WORKGROUP:
					$bc[] = ['text' => $this->translator->trans($workspace->getWorkgroup($link['wg'])->getName(), [], 'pages'), 'link' => ''];
			}
		}
		return $bc;
	}
}
