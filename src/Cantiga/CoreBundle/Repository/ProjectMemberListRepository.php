<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Tomasz Jedrzejewski.
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
namespace Cantiga\CoreBundle\Repository;

use Doctrine\DBAL\Connection;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\Components\Hierarchy\MembershipRoleResolverInterface;
use Cantiga\Metamodel\Transaction;

/**
 * Shows the list of members of the given project.
 */
class ProjectMemberListRepository extends AbstractMemberListRepository
{
	public function __construct(Connection $conn, Transaction $transaction, MembershipRoleResolverInterface $roleResolver)
	{
		parent::__construct($conn, $transaction, $roleResolver);
	}

	protected function entityColumn()
	{
		return 'projectId';
	}

	protected function entityName()
	{
		return 'Project';
	}

	protected function membershipTable()
	{
		return CoreTables::PROJECT_MEMBER_TBL;
	}
}
