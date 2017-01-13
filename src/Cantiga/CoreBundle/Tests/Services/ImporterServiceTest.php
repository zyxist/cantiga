<?php
/*
 * This file is part of Cantiga Project. Copyright 2017 Cantiga contributors.
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
 * along with Cantiga; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
declare(strict_types=1);
namespace Cantiga\CoreBundle\Tests\Services;
use Cantiga\Components\Hierarchy\PlaceLoaderInterface;
use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;
use PHPUnit\Framework\TestCase;
use Cantiga\Components\Hierarchy\MembershipStorageInterface;
use Cantiga\CoreBundle\Services\ImporterService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ImporterServiceTest extends TestCase
{
	private $importer;
	private $membershipStorage;
	private $translator;
	private $tokenStorage;
	private $loader;

	private $membership;
	private $token;
	private $user;
	private $place;

	public function setUp()
	{
		$this->membershipStorage = $this->getMockBuilder(MembershipStorageInterface::class)->getMock();
		$this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
		$this->translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
		$this->loader = $this->getMockBuilder(PlaceLoaderInterface::class)->getMock();

		$this->membership = $this->getMockBuilder(Membership::class)->disableOriginalConstructor()->getMock();
		$this->token = $this->getMockBuilder(TokenInterface::class)->getMock();
		$this->user = $this->getMockBuilder(CantigaUserRefInterface::class)->getMock();
		$this->place = $this->getMockBuilder(HierarchicalInterface::class)->getMock();

		$this->tokenStorage->method('getToken')
			->will($this->returnValue($this->token));
		$this->token->method('getUser')
			->will($this->returnValue($this->user));
		$this->membershipStorage->method('getMembership')
			->will($this->returnValue($this->membership));
		$this->membershipStorage->method('hasMembership')
			->will($this->returnValue(true));
		$this->membership->method('getPlace')
			->will($this->returnValue($this->place));

		$this->importer = new ImporterService($this->membershipStorage, $this->tokenStorage, $this->translator);
		$this->importer->addPlaceLoader('Foo', $this->loader);
	}

	public function testCheckImportIsNotAvailableIfNoParentPlace()
	{
		// Given
		$this->currentPlaceType('Foo');
		$this->loaderWillReturn(null);

		// When
		$result = $this->importer->isImportAvailable();

		// Then
		$this->assertFalse($result);
	}

	public function testCheckImportIsAvailableIfParentPlaceSpecified()
	{
		// Given
		$this->currentPlaceType('Foo');
		$sourcePlace = $this->getMockBuilder(HierarchicalInterface::class)->getMock();
		$this->loaderWillReturn($sourcePlace);

		// When
		$result = $this->importer->isImportAvailable();

		// Then
		$this->assertTrue($result);
	}

	public function testImportLabelGeneration()
	{
		// Given
		$this->currentPlaceType('Foo');
		$this->loaderWillReturn($this->place);
		$this->place->expects($this->once())
			->method('isRoot')
			->will($this->returnValue(true));
		$this->place->expects($this->once())
			->method('getName')
			->will($this->returnValue('XYZ'));
		$this->translator->expects($this->once())
			->method('trans')
			->with('Import from 0', [0 => 'XYZ'], 'general')
			->will($this->returnValue('Import from XYZ'));

		// When
		$label = $this->importer->getImportLabel();

		// Then
		$this->assertEquals('Import from XYZ', $label);
	}

	public function testImportLabelGenerationNoSuchPlace()
	{
		// Given
		$this->currentPlaceType('Foo');
		$this->loaderWillReturn(null);

		// When
		$label = $this->importer->getImportLabel();

		// Then
		$this->assertEquals('', $label);
	}

	public function testInvalidPlaceType()
	{
		// Given
		$sourcePlace = $this->getMockBuilder(HierarchicalInterface::class)->getMock();
		$this->currentPlaceType('Bar');

		try {
			// When
			$this->importer->getImportSource();
			$this->fail('Exception not thrown');
		} catch (\LogicException $exception) {
			// Then
			$this->assertEquals('Place loader not registered for place type \'Bar\'', $exception->getMessage());
		}
	}

	public function testGetImportSourceAndNoSuchPlace()
	{
		// Given
		$sourcePlace = $this->getMockBuilder(HierarchicalInterface::class)->getMock();
		$this->currentPlaceType('Foo');
		$this->loaderWillReturn(null);

		try {
			// When
			$this->importer->getImportSource();
			$this->fail('Exception not thrown');
		} catch (\LogicException $exception) {
			// Then
			$this->assertEquals('There is no place to import anything from.', $exception->getMessage());
		}
	}

	private function loaderWillReturn(?HierarchicalInterface $place): void
	{
		$this->loader->expects($this->once())
			->method('loadPlaceForImport')
			->with($this->place, $this->user)
			->will($this->returnValue($place));
	}

	private function currentPlaceType(string $type): void
	{
		$this->place->method('getTypeName')
			->will($this->returnValue($type));
	}
}
