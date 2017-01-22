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
namespace Cantiga\AppTextBundle\Tests\Services;
use Cantiga\AppTextBundle\Database\AppTextAdapter;
use Cantiga\AppTextBundle\Entity\AppTextView;
use Cantiga\AppTextBundle\Services\AppTextHolder;
use Cantiga\Components\Application\LocaleProviderInterface;
use Cantiga\Components\Hierarchy\HierarchicalInterface;
use PHPUnit\Framework\TestCase;

class AppTextHolderTest extends TestCase
{
	const PROJECT_ID = 1;

	private $holder;
	private $localeProvider;
	private $project;
	private $adapter;

	public function setUp()
	{
		$this->adapter = $this->getMockBuilder(AppTextAdapter::class)->disableOriginalConstructor()->getMock();
		$this->localeProvider = $this->getMockBuilder(LocaleProviderInterface::class)->getMock();
		$this->project = $this->getMockBuilder(HierarchicalInterface::class)->getMock();
		$this->project->method('getId')
			->will($this->returnValue(self::PROJECT_ID));

		$this->holder = new AppTextHolder($this->adapter, $this->localeProvider);
	}

	public function testFindGlobalTextSuccessful()
	{
		// Given
		$this->expectLocale('en');
		$this->expectGlobalText('foo', 'en', ['title' => 'Foo', 'content' => 'Bar', 'projectId' => null]);

		// When
		$result = $this->holder->findText('foo');

		// Then
		$this->assertTrue($result instanceof AppTextView);
		$this->assertEquals('Foo', $result->getTitle());
		$this->assertEquals('Bar', $result->getContent());
		$this->assertTrue($result->isPresent());
	}

	public function testFindGlobalTextUnsuccessful()
	{
		// Given
		$this->expectLocale('en');
		$this->expectGlobalText('foo', 'en', false);

		// When
		$result = $this->holder->findText('foo');

		// Then
		$this->assertTrue($result instanceof AppTextView);
		$this->assertEquals('', $result->getTitle());
		$this->assertEquals('', $result->getContent());
		$this->assertFalse($result->isPresent());
	}

	public function testFindProjectTextLocalFound()
	{
		// Given
		$this->expectLocale('en');
		$this->expectProjectText('foo', 'en', self::PROJECT_ID, [
			['title' => 'Local', 'content' => 'LC', 'projectId' => self::PROJECT_ID],
			['title' => 'Global', 'content' => 'GC', 'projectId' => null],
		]);

		// When
		$result = $this->holder->findText('foo', $this->project);

		// Then
		$this->assertTrue($result instanceof AppTextView);
		$this->assertEquals('Local', $result->getTitle());
		$this->assertEquals('LC', $result->getContent());
		$this->assertTrue($result->isPresent());
	}

	public function testFindProjectTextLocalFoundOrderDoesNotMatter()
	{
		// Given
		$this->expectLocale('en');
		$this->expectProjectText('foo', 'en', self::PROJECT_ID, [
			['title' => 'Global', 'content' => 'GC', 'projectId' => null],
			['title' => 'Local', 'content' => 'LC', 'projectId' => self::PROJECT_ID],
		]);

		// When
		$result = $this->holder->findText('foo', $this->project);

		// Then
		$this->assertTrue($result instanceof AppTextView);
		$this->assertEquals('Local', $result->getTitle());
		$this->assertEquals('LC', $result->getContent());
		$this->assertTrue($result->isPresent());
	}

	public function testFindProjectTextGlobalFound()
	{
		// Given
		$this->expectLocale('en');
		$this->expectProjectText('foo', 'en', self::PROJECT_ID, [
			['title' => 'Global', 'content' => 'GC', 'projectId' => null],
		]);

		// When
		$result = $this->holder->findText('foo', $this->project);

		// Then
		$this->assertTrue($result instanceof AppTextView);
		$this->assertEquals('Global', $result->getTitle());
		$this->assertEquals('GC', $result->getContent());
		$this->assertTrue($result->isPresent());
	}

	public function testFindProjectTextUnsuccessful()
	{
		// Given
		$this->expectLocale('en');
		$this->expectProjectText('foo', 'en', self::PROJECT_ID, []);

		// When
		$result = $this->holder->findText('foo', $this->project);

		// Then
		$this->assertTrue($result instanceof AppTextView);
		$this->assertEquals('', $result->getTitle());
		$this->assertEquals('', $result->getContent());
		$this->assertFalse($result->isPresent());
	}

	private function expectLocale(string $locale): void
	{
		$this->localeProvider->method('findLocale')
			->will($this->returnValue($locale));
	}

	private function expectGlobalText(string $place, string $locale, $result)
	{
		$this->adapter->expects($this->once())
			->method('selectGlobalText')
			->with($place, $locale)
			->will($this->returnValue($result));
	}

	private function expectProjectText(string $place, string $locale, int $id, array $result)
	{
		$this->adapter->expects($this->once())
			->method('selectMatchingTexts')
			->with($place, $locale, $id)
			->will($this->returnValue($result));
	}
}
