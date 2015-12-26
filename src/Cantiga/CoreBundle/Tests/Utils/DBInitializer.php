<?php
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
