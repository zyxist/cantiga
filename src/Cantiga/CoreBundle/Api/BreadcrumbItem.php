<?php
namespace Cantiga\CoreBundle\Api;

/**
 * Description of BreadcrumbEntry
 *
 * @author Tomasz Jędrzejewski
 */
class BreadcrumbItem
{
	private $text;
	private $route;
	private $args;
	private $isEntry = false;
	
	public static function create($text, $route, array $args = [])
	{
		return new BreadcrumbItem($text, $route, $args);
	}
	
	public function __construct($text, $route, array $args = [])
	{
		$this->text = $text;
		$this->route = $route;
		$this->args = $args;
	}
	
	public function entry()
	{
		$this->isEntry = true;
		return $this;
	}
	
	public function getText()
	{
		return $this->text;
	}

	public function getRoute()
	{
		return $this->route;
	}

	public function getArgs()
	{
		return $this->args;
	}

	public function isEntry()
	{
		return $this->isEntry;
	}
}
