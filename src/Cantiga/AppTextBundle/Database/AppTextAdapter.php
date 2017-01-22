<?php
/*
 * This file is part of Cantiga Project. Copyright 2016-2017 Cantiga contributors.
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
namespace Cantiga\AppTextBundle\Database;

use Cantiga\CoreBundle\CoreTables;
use Doctrine\DBAL\Connection;

class AppTextAdapter
{
	private $conn;

	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}

	public function selectGlobalText(string $place, string $locale)
	{
		return $this->conn->fetchAssoc('SELECT * FROM `'.CoreTables::APP_TEXT_TBL.'` '
			. 'WHERE `place` = :place AND `locale` = :locale AND `projectId` IS NULL',
			[':place' => $place, ':locale' => $locale]);
	}

	public function selectMatchingTexts(string $place, string $locale, int $projectId): array
	{
		return $this->conn->fetchAll('SELECT * FROM `'.CoreTables::APP_TEXT_TBL.'` '
			. 'WHERE `place` = :place AND `locale` = :locale AND '
			. '(`projectId` = :projectId OR `projectId` IS NULL)',
			[':place' => $place, ':locale' => $locale, ':projectId' => $projectId]);
	}
}
