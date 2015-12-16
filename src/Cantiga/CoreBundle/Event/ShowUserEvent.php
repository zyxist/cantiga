<?php
namespace Cantiga\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Emitted by the navbar controller, the event is used to pass the info about the currently
 * logged user, so that we can display a profile information for him/her.
 *
 * @author Tomasz Jędrzejewski
 */
class ShowUserEvent extends Event
{
    /**
     * @var UserInterface
     */
    protected $user;

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }
}
