<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\DiscussionBundle\Database\DiscussionAdapter;
use Cantiga\DiscussionBundle\Repository\ChannelRepository;
use Cantiga\Metamodel\Transaction;

class ChannelRepositoryTest extends \PHPUnit_Framework_TestCase
{
	private $transaction;
	private $adapter;
	private $repository;
	private $project;
	
	public function setUp()
	{
		$this->adapter = $this->getMockBuilder(DiscussionAdapter::class)->disableOriginalConstructor()->getMock();
		$this->transaction = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();
		$this->repository = new ChannelRepository($this->adapter, $this->transaction);
		$this->project = new Project();
		$this->project->setId(13);
	}
	
	public function testFetchingProjectVisibleChannels()
	{
		// Given
		$entity = new Project();
		$this->repository->setProject($this->project);
		$this->adapter->method('findVisibleChannels')
			->with($this->project->getId(), 'projectVisible')
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
		$this->repository->setProject($this->project);
		$this->adapter->method('findVisibleChannels')
			->with($this->project->getId(), 'groupVisible')
			->will($this->returnValue(['foo' => 'bar']));
		
		// When
		$items = $this->repository->findWorkspaceChannels($entity);
		
		// Then
		$this->assertEquals(['foo' => 'bar'], $items);
	}
	
	public function testFetchingAreaVisibleChannels()
	{
		// Given
		$entity = new Area();
		$this->repository->setProject($this->project);
		$this->adapter->method('findVisibleChannels')
			->with($this->project->getId(), 'areaVisible')
			->will($this->returnValue(['foo' => 'bar']));
		
		// When
		$items = $this->repository->findWorkspaceChannels($entity);
		
		// Then
		$this->assertEquals(['foo' => 'bar'], $items);
	}
}
