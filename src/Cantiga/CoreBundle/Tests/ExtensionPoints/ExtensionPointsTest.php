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
namespace Cantiga\CoreBundle\Tests\ExtensionPoints;

use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointFilter;
use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPoints;
use Cantiga\CoreBundle\Api\ExtensionPoints\Implementation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExtensionPointsTest extends TestCase
{
	private $container;
	/**
	 * @var ExtensionPoints
	 */
	private $extensionPoints;
	
	public function setUp()
	{
		$this->container = $this->getMockBuilder(ContainerInterface::class)->getMock();
		$this->extensionPoints = new ExtensionPoints($this->container);
	}
	
	public function testFindingMultipleImplementations()
	{
		// Given
		$svc1 = new \stdclass;
		$svc2 = new \stdclass;
		$this->container->expects($this->exactly(2))
			->method('get')
			->withConsecutive()
			->will($this->returnValueMap([
				['svc1', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $svc1],
				['svc2', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $svc2]
			]));
		
		$this->extensionPoints->register(new Implementation('ext.point', 'foo', 'svc1', 'Implementation 1'));
		$this->extensionPoints->register(new Implementation('ext.point', 'bar', 'svc2', 'Implementation 2'));
		$this->extensionPoints->register(new Implementation('ext.point.other', 'foo', 'svc3', 'Implementation 3'));
		$filter = new ExtensionPointFilter(['foo', 'bar']);
		
		// When
		$result = $this->extensionPoints->findImplementations('ext.point', $filter);
		
		// Then
		$this->assertEquals([$svc1, $svc2], $result);
	}
	
	public function testGetImplementationReturnsFirstMatchingImplementation()
	{
		// Given
		$svc1 = new \stdclass;
		$this->container->expects($this->once())
			->method('get')
			->will($this->returnValue($svc1));
		
		$this->extensionPoints->register(new Implementation('ext.point', 'foo', 'svc1', 'Implementation 1'));
		$this->extensionPoints->register(new Implementation('ext.point', 'bar', 'svc2', 'Implementation 2'));
		$this->extensionPoints->register(new Implementation('ext.point.other', 'foo', 'svc3', 'Implementation 3'));
		$filter = new ExtensionPointFilter(['foo', 'bar']);
		
		// When
		$result = $this->extensionPoints->getImplementation('ext.point', $filter);
		
		// Then
		$this->assertSame($svc1, $result);
	}
	
	public function testGetImplementationThrowsExceptionIfNotMatching()
	{
		// Given	
		$this->extensionPoints->register(new Implementation('ext.point', 'foo', 'svc1', 'Implementation 1'));
		$this->extensionPoints->register(new Implementation('ext.point', 'bar', 'svc2', 'Implementation 2'));
		$this->extensionPoints->register(new Implementation('ext.point.other', 'foo', 'svc3', 'Implementation 3'));
		$filter = new ExtensionPointFilter(['moo']);
		
		try {
			// When
			$this->extensionPoints->getImplementation('ext.point', $filter);
			$this->fail('Exception should be thrown');
		} catch (\RuntimeException $exception) {
			// Then
			$this->assertEquals('No implementation for the extension point \'ext.point\'', $exception->getMessage());
		}
	}
}
