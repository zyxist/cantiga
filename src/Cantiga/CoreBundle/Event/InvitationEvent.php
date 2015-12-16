<?php
namespace Cantiga\CoreBundle\Event;
use Cantiga\CoreBundle\Entity\Invitation;
use Symfony\Component\EventDispatcher\Event;

/**
 * Informs about new invitations added to the database. The basic
 * use case for this event is sending an e-mail notification to the
 * user.
 *
 * @author Tomasz Jędrzejewski
 */
class InvitationEvent extends Event
{
	private $invitation;
	
	public function __construct(Invitation $invitation)
	{
		$this->invitation = $invitation;
	}
	
	/**
	 * 
	 * @return Invitation
	 */
	public function getInvitation()
	{
		return $this->invitation;
	}
}
