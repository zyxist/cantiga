<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga contributors.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
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
