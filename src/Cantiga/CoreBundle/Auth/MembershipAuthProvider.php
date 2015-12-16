<?php
namespace Cantiga\CoreBundle\Auth;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Description of MembershipAuthProvider
 *
 * @author Tomasz Jędrzejewski
 */
class MembershipAuthProvider implements AuthenticationProviderInterface
{
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider, $cacheDir)
    {
        $this->userProvider = $userProvider;
    }

	public function authenticate(TokenInterface $token)
	{
		
	}

	public function supports(TokenInterface $token)
	{
		
	}

}
