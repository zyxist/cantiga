<?php
namespace Cantiga\Metamodel\Capabilities;

use Doctrine\DBAL\Connection;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\MembershipRole;
use Cantiga\Metamodel\MembershipRoleResolver;

/**
 * @author Tomasz Jędrzejewski
 */
interface MembershipEntityInterface extends IdentifiableInterface
{
	/**
	 * The method shall return all the members of the current entity, given as an array to present through JSON.
	 * 
	 * @param Connection $conn
	 * @return array
	 */
	public function findMembers(Connection $conn, MembershipRoleResolver $roleResolver);
	public function joinMember(Connection $conn, User $user, MembershipRole $role, $note);
	public function editMember(Connection $conn, User $user, MembershipRole $role, $note);
	public function removeMember(Connection $conn, User $user);
}
