<?php
namespace Cantiga\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Cantiga\CoreBundle\Entity\User;

/**
 * Description of UserEvent
 *
 * @author Tomasz Jędrzejewski
 */
class UserEvent extends Event
{
	/**
	 * @var User
	 */
	private $user;
	
	public function __construct(User $user)
	{
		$this->user = $user;
	}
	
	public function getUser()
	{
		return $this->user;
	}
}
