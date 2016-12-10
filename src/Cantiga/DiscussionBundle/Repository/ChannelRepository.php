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
namespace Cantiga\DiscussionBundle\Repository;

use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\DiscussionBundle\Database\DiscussionAdapter;
use Cantiga\DiscussionBundle\Entity\Channel;
use Cantiga\DiscussionBundle\Entity\Subchannel;
use Cantiga\Components\Hierarchy\MembershipEntityInterface;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Transaction;

class ChannelRepository
{
	/**
	 * @var DiscussionAdapter 
	 */
	private $adapter;
	/**
	 * @var Transaction
	 */
	private $transaction;
	/**
	 * @var Project
	 */
	private $project;
	
	public function __construct(DiscussionAdapter $adapter, Transaction $transaction)
	{
		$this->adapter = $adapter;
		$this->transaction = $transaction;
	}
	
	public function setProject(Project $project)
	{
		$this->project = $project;
	}
	
	public function getItem(int $id): Channel
	{
		$this->transaction->requestTransaction();
		$item = Channel::fetchByProject($this->adapter->getConnection(), $id, $this->project);
		if(false === $item) {
			$this->transaction->requestRollback();
			throw new ItemNotFoundException('The specified item has not been found.', $id);
		}
		return $item;
	}
	
	public function getSubchannel(int $channelId, HierarchicalInterface $context): Subchannel
	{
		$this->transaction->requestTransaction();
		try {
			$channel = Channel::fetchByProject($this->adapter->getConnection(), $channelId, $this->project);
			$subchannel = Subchannel::lazilyFetchByChannel($this->adapter, $channel, $context);
			if (empty($subchannel)) {
				throw new ItemNotFoundException('The specified item has not been found.', $channelId); 
			}
			return $subchannel;
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function publish(Subchannel $subchannel, $content, User $user, HierarchicalInterface $context)
	{
		$this->transaction->requestTransaction();
		try {
			return $subchannel->publish($this->adapter, $content, $user, $context);			
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function findWorkspaceChannels(HierarchicalInterface $entity): array
	{
		$entityIds = [$entity->getPlace()->getId()];
		if ($entity instanceof Project) {
			$visibility = 'projectVisible';
			$minLevel = 0;
		} elseif ($entity instanceof Group) {
			$visibility = 'groupVisible';
			$minLevel = 1;
			$entityIds[] = $entity->getProject()->getPlace()->getId();
		} elseif ($entity instanceof Area) {
			$visibility = 'areaVisible';
			$minLevel = 2;
			$entityIds[] = $entity->getProject()->getPlace()->getId();
			if (null !== $entity->getGroup()) {
				$entityIds[] = $entity->getGroup()->getPlace()->getId();
			}
		}
		return $this->adapter->findVisibleChannels($this->project->getId(), $entityIds, $visibility, $minLevel);
	}
}
