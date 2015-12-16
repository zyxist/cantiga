<?php
namespace Cantiga\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Cantiga\CoreBundle\Entity\UserRegistration;

/**
 * Description of UserRegistrationEvent
 *
 * @author Tomasz Jędrzejewski
 */
class UserRegistrationEvent extends Event
{
	/**
	 * @var UserRegistration 
	 */
	private $registration;
	
	public function __construct(UserRegistration $registration)
	{
		$this->registration = $registration;
	}
	
	public function getRegistration()
	{
		return $this->registration;
	}
}
