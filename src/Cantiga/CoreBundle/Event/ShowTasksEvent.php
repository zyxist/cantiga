<?php
namespace Cantiga\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
/**
 * @author Tomasz Jędrzejewski
 */
class ShowTasksEvent extends Event
{
	private $tasks = array();
	
	public function addTask($task)
	{
		$this->tasks[] = $task;
	}
	
	public function hasTasks()
	{
		return sizeof($this->tasks) > 0;
	}
	
	public function getTasks()
	{
		return $this->tasks;
	}
}
