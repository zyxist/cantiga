<?php
namespace Cantiga\Metamodel;

use Cantiga\CoreBundle\Entity\Project;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author Tomasz Jędrzejewski
 */
class MembershipToken extends UsernamePasswordToken
{
	/**
	 * @var Membership
	 */
	private $membership;
	/**
	 * @var Project
	 */
	private $masterProject;
	
	public function __construct($sourceToken, Membership $membership, Project $project)
	{
		if (!($sourceToken instanceof UsernamePasswordToken) && !($sourceToken instanceof RememberMeToken)) {
			throw new AccessDeniedException('Invalid authentication token');
		}
		parent::__construct($sourceToken->getUser(), $sourceToken->getCredentials(), $sourceToken->getProviderKey(), $sourceToken->getUser()->getRoles());
		$this->membership = $membership;
		$this->masterProject = $project;
	}
	
	public function getMembershipEntity()
	{
		return $this->membership->getItem();
	}
	
	public function getMembership()
	{
		return $this->membership;
	}

	public function getMasterProject()
	{
		return $this->masterProject;
	}
	
	public function getCredentials()
	{
		return null;
	}
}
