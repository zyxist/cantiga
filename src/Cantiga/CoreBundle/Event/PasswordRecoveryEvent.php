<?php
namespace Cantiga\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Cantiga\CoreBundle\Entity\PasswordRecoveryRequest;

/**
 * Description of PasswordRecoveryEvent
 *
 * @author Tomasz Jędrzejewski
 */
class PasswordRecoveryEvent extends Event
{
	private $request;
	
	public function __construct(PasswordRecoveryRequest $request)
	{
		$this->request = $request;
	}
	
	/**
	 * @return PasswordRecoveryRequest
	 */
	public function getRequest()
	{
		return $this->request;
	}
}
