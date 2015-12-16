<?php
namespace Cantiga\CoreBundle\Entity;

/**
 * Chat message that can be used in various places.
 *
 * @author Tomasz Jędrzejewski
 */
class Message
{
	private $user;
	private $createdAt;
	private $message;
	
	public function __construct(User $user, $message)
	{
		$this->user = $user;
		$this->createdAt = time();
		$this->message = $message;
	}
	
	public function getUser()
	{
		return $this->user;
	}

	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	public function getMessage()
	{
		return $this->message;
	}
}
