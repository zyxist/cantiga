<?php
namespace Cantiga\CoreBundle\Api;

/**
 * Groups several work items under a single label and icon. The main menu of the workspace
 * consists of one to several workgroups.
 *
 * @author Tomasz Jędrzejewski
 */
class Workgroup
{
	private $key;
	private $name;
	private $icon;
	private $order;
	
	public function __construct($key, $name, $icon, $order = 0)
	{
		$this->key = $key;
		$this->name = $name;
		$this->icon = $icon;
		$this->order = $order;
	}
	
	public function getKey()
	{
		return $this->key;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getIcon()
	{
		return $this->icon;
	}

	public function getOrder()
	{
		return $this->order;
	}
}
