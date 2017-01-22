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
namespace Cantiga\AppTextBundle\Services;

use Cantiga\AppTextBundle\Entity\AppTextView;
use Cantiga\AppTextBundle\Database\AppTextAdapter;
use Cantiga\Components\Application\LocaleProviderInterface;
use Cantiga\Components\Application\AppTextInterface;
use Cantiga\Components\Application\AppTextHolderInterface;
use Cantiga\Components\Hierarchy\HierarchicalInterface;

class AppTextHolder implements AppTextHolderInterface
{
	private $adapter;
	private $localeProvider;

	public function __construct(AppTextAdapter $adapter, LocaleProviderInterface $localeProvider)
	{
		$this->adapter = $adapter;
		$this->localeProvider = $localeProvider;
	}

	public function findText(string $key, ?HierarchicalInterface $place = null): AppTextInterface
	{
		if (null === $place) {
			$data = $this->adapter->selectGlobalText($key, $this->localeProvider->findLocale());
		} else {
			$items = $this->adapter->selectMatchingTexts($key, $this->localeProvider->findLocale(), $place->getId());
			$data = $this->selectLocalText($items, $place);
			if (false === $data) {
				$data = $this->selectGlobalText($items);
			}
		}
		return $this->constructTextView($data);
	}

	private function selectLocalText(array $items, HierarchicalInterface $place)
	{
		foreach ($items as $item) {
			if ($item['projectId'] == $place->getId()) {
				return $item;
			}
		}
		return false;
	}

	private function selectGlobalText(array $items)
	{
		foreach ($items as $item) {
			if (empty($item['projectId'])) {
				return $item;
			}
		}
		return false;
	}

	private function constructTextView($data): AppTextView
	{
		if(false === $data) {
			return AppTextView::emptyText();
		}
		return new AppTextView($data['title'], $data['content']);
	}
}
