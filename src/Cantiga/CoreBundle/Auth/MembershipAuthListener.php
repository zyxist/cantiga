<?php
namespace Cantiga\CoreBundle\Auth;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;


/**
 * Description of MembershipAuthListener
 *
 * @author Tomasz Jędrzejewski
 */
class MembershipAuthListener implements ListenerInterface
{
    protected $tokenStorage;
    protected $authenticationManager;

    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
    }

	public function handle(GetResponseEvent $event)
	{
		$request = $event->getRequest();
		if (!($ml = $request->attributes->get('_membership_loader')) || !($slug = $request->get('slug'))) {
			return;
		}
		
		
	}
}
