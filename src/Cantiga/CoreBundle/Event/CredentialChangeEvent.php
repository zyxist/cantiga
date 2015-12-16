<?php
namespace Cantiga\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Cantiga\CoreBundle\Entity\CredentialChangeRequest;

/**
 * @author Tomasz Jędrzejewski
 */
class CredentialChangeEvent extends Event
{
	private $changeRequest;
	
	public function __construct(CredentialChangeRequest $request)
	{
		$this->changeRequest = $request;
	}
	
	/**
	 * @return CredentialChangeRequest
	 */
	public function getChangeRequest()
	{
		return $this->changeRequest;
	}
}
