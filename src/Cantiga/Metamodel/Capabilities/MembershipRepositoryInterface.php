<?php
namespace Cantiga\Metamodel\Capabilities;
use Cantiga\CoreBundle\Entity\Invitation;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Capabilities\MembershipEntityInterface;
use Cantiga\Metamodel\MembershipRole;

/**
 * @author Tomasz Jędrzejewski
 */
interface MembershipRepositoryInterface
{
	public function joinMember(MembershipEntityInterface $identifiable, User $user, MembershipRole $role, $note);
	public function editMember(MembershipEntityInterface $identifiable, User $user, MembershipRole $role, $note);
	public function removeMember(MembershipEntityInterface $identifiable, User $user);
	public function acceptInvitation(Invitation $invitation);
	public function clearMembership(User $user);
}
