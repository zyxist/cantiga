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
namespace Cantiga\DiscussionBundle\Tests\Repository;

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Place;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\DiscussionBundle\Database\DiscussionAdapter;
use Cantiga\DiscussionBundle\Repository\ChannelRepository;
use Cantiga\Metamodel\Transaction;
use PHPUnit\Framework\TestCase;

class ChannelRepositoryTest extends TestCase
{
	private $transaction;
	private $adapter;
	private $repository;
	private $project;
	private $checkedEntity;
	
	protected function setUp()
	{
		$this->adapter = $this->getMockBuilder(DiscussionAdapter::class)->disableOriginalConstructor()->getMock();
		$this->transaction = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();
		$this->repository = new ChannelRepository($this->adapter, $this->transaction);
		$this->project = new Project();
		$this->project->setId(13);
		
		$projectEntity = new Place();
		$projectEntity->setId(12);
		$this->project->setPlace($projectEntity);
		
		$this->checkedEntity = new Place();
		$this->checkedEntity->getId(26);
	}
	
	public function testFetchingProjectVisibleChannels()
	{
		// Given
		$entity = new Project();
		$entity->setPlace($this->checkedEntity);
		$this->repository->setProject($this->project);
		$this->adapter->expects($this->once())
			->method('findVisibleChannels')
			->with($this->project->getId(), [$entity->getPlace()->getId()], 'projectVisible', 0)
			->will($this->returnValue(['foo' => 'bar']));
		
		// When
		$items = $this->repository->findWorkspaceChannels($entity);
		
		// Then
		$this->assertEquals(['foo' => 'bar'], $items);
	}
	
	public function testFetchingGroupVisibleChannels()
	{
		// Given
		$entity = new Group();
		$entity->setPlace($this->checkedEntity);
		$entity->setProject($this->project);
		$this->repository->setProject($this->project);
		$this->adapter->expects($this->once())
			->method('findVisibleChannels')
			->with($this->project->getId(), [$entity->getPlace()->getId(), $this->project->getPlace()->getId()], 'groupVisible', 1)
			->will($this->returnValue(['foo' => 'bar']));
		
		// When
		$items = $this->repository->findWorkspaceChannels($entity);
		
		// Then
		$this->assertEquals(['foo' => 'bar'], $items);
	}
	
	public function testFetchingUngroupedAreaVisibleChannels()
	{
		// Given
		$entity = new Area();
		$entity->setPlace($this->checkedEntity);
		$entity->setProject($this->project);
		$this->repository->setProject($this->project);
		$this->adapter->expects($this->once())
			->method('findVisibleChannels')
			->with($this->project->getId(), [$entity->getPlace()->getId(), $this->project->getPlace()->getId()], 'areaVisible', 2)
			->will($this->returnValue(['foo' => 'bar']));
		
		// When
		$items = $this->repository->findWorkspaceChannels($entity);
		
		// Then
		$this->assertEquals(['foo' => 'bar'], $items);
	}
	
	public function testFetchingGroupedAreaVisibleChannels()
	{
		// Given
		$group = new Group();
		$groupEntity = new Place();
		$groupEntity->setId(17);
		$group->setPlace($groupEntity);
		
		$entity = new Area();
		$entity->setGroup($group);
		$entity->setPlace($this->checkedEntity);
		$entity->setProject($this->project);
		$this->repository->setProject($this->project);
		$this->adapter->expects($this->once())
			->method('findVisibleChannels')
			->with($this->project->getId(), [$entity->getPlace()->getId(), $this->project->getPlace()->getId(), $group->getPlace()->getId()], 'areaVisible', 2)
			->will($this->returnValue(['foo' => 'bar']));
		
		// When
		$items = $this->repository->findWorkspaceChannels($entity);
		
		// Then
		$this->assertEquals(['foo' => 'bar'], $items);
	}
}
