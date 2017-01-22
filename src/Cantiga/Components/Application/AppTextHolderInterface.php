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
declare(strict_types=1);
namespace Cantiga\Components\Application;

use Cantiga\Components\Hierarchy\HierarchicalInterface;

/**
 * API for fetching the application texts that can be displayed in the Cantiga
 * UI. Concrete backends may provide additional features, such as internationalization.
 */
interface AppTextHolderInterface
{
	/**
	 * Finds the given text in the application text repository. The text may be associated
	 * with the given place (optional). The method shall always return a value: if the text
	 * does not exist, a default object shall be returned.
	 */
	public function findText(string $id, ?HierarchicalInterface $place = null): AppTextInterface;
}
