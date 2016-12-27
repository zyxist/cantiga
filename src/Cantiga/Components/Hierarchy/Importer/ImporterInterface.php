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
namespace Cantiga\Components\Hierarchy\Importer;

use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\CoreBundle\Api\Actions\QuestionHelper;

/**
 * Helper service for handling imports from old projects.
 */
interface ImporterInterface {
	/**
	 * Checks whether it is possible to import anything from the current project.
	 */
	public function isImportAvailable(): bool;
	/**
	 * Generates a label that can be used for creating "Import from XYZ" button.
	 */
	public function getImportLabel(): string;
	
	public function getImportSource(): HierarchicalInterface;
	public function getImportDestination(): HierarchicalInterface;
	/**
	 * Constructs a question helper for confirming the import. The question
	 * will be automatically translated, using <tt>messages</tt> translation
	 * domain.
	 */
	public function getImportQuestion(string $pageTitle, string $question): QuestionHelper;
}
