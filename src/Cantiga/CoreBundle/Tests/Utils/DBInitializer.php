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
namespace Cantiga\CoreBundle\Tests\Utils;

use Symfony\Component\Console\Application;

/**
 * Helper class for managing the SQL files in the tests.
 *
 * @author Tomasz Jędrzejewski
 */
class DBInitializer
{
	const MIGRATION_FILE = '/migration.txt';
	
	private $application;
	private $dbFiles;
	
	public function __construct(Application $app, $dbFiles)
	{
		$this->application = $app;
		$this->dbFiles = $dbFiles;
	}
	
	public function getMigrationFiles()
	{
		if(!file_exists($this->dbFiles.self::MIGRATION_FILE)) {
			throw new \LogicException('There is no migration file: '.$this->dbFiles.self::MIGRATION_FILE);
		}
		
		$output = array();
		foreach(file($this->dbFiles.self::MIGRATION_FILE) as $line) {
			$line = trim($line);
			if($line != '') {
				$migrationFile = $this->dbFiles.'/schema/'.$line;
				if(!file_exists($migrationFile)) {
					throw new \LogicException('The migration file '.$migrationFile.' does not exist!');
				}
				$output[] = $migrationFile;
			}
		}
		return $output;
	}
	
	public function fixture($fixture)
	{
		$fixtureFile = $this->dbFiles.'/fixtures/'.$fixture;
		if(!file_exists($fixtureFile)) {
			throw new \LogicException('There is no fixture file: '.$fixtureFile);
		}
		return $fixtureFile;
	}
}
