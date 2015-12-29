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
namespace Cantiga\CoreBundle\Tests\Utils;

use AppKernel;
use Cantiga\Metamodel\Transaction;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Tools\Console\Command\ImportCommand;
use Doctrine\DBAL\Tools\Console\Command\ReservedWordsCommand;
use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;

require_once dirname(__DIR__).'/../../../../app/AppKernel.php';

/**
 * Common foundation for the tests of components that need an access to the database.
 * It deals with initializing the DB, loading the fixtures, etc.
 *
 * @author Tomasz Jędrzejewski
 */
class DatabaseTestCase extends \PHPUnit_Framework_TestCase {
    /**
	 * @var Symfony\Component\HttpKernel\AppKernel
	 */
	protected static $kernel;
	
	/**
	 * @var Application
	 */
	protected static $application;

	/**
	 * @var Container
	 */
	protected static $container;

	/**
	 * @var Connection 
	 */
	protected static $conn;
	/**
	 * @var Transaction
	 */
	protected static $transaction;
	/**
	 * @var EventDispatcher 
	 */
	protected static $eventDispatcher;
	/**
	 * @var DBInitializer 
	 */
	protected static $dbInitializer;
	
	private static $loadedFixtures = [];
	
	private static $dbReused = false;
	
	public static function setUpBeforeClass() {
		self::initializeEnvironment();
		self::initializeDatabase();
		static::customSetup();
	}
	
	private static function initializeEnvironment()
	{
		// Boot the AppKernel in the test environment and with the debug.
		self::$kernel = new AppKernel('test', true);
		self::$kernel->boot();
		
		// Create the application for command execution
		$helperSet = new HelperSet();
		
		self::$application = new Application(self::$kernel);
		self::$application->setHelperSet($helperSet);
		self::$application->addCommands(self::enhance($helperSet, array(
			new RunSqlCommand(),
			new ImportCommand(),
			new ReservedWordsCommand(),
			new CreateDatabaseDoctrineCommand(),
			new DropDatabaseDoctrineCommand(),
		)));
		self::$application->setAutoExit(false);
		
		// Store the container and the DB connection in test case properties
		self::$container = self::$kernel->getContainer();
		self::$conn = self::$container->get('database_connection');
		self::$transaction = self::$container->get('cantiga.transaction');
		self::$eventDispatcher = new EventDispatcher();
		$helperSet->set(new ConnectionHelper(self::$conn), 'db');
		
		if(SharedResources::$dbInitializer !== null) {
			self::$dbInitializer = SharedResources::$dbInitializer;
			self::$dbReused = true;
		} else {
			SharedResources::$dbInitializer = self::$dbInitializer = new DBInitializer(self::$application, self::$container->getParameter('db_files'));
		}
	}
	
	private static final function initializeDatabase()
	{
		if(!self::$dbReused) {
			self::runCommand('doctrine:database:drop', array('--force' => true));
			self::runCommand('doctrine:database:create');

			self::$conn->close();
			self::$conn->connect();
			foreach(self::$dbInitializer->getMigrationFiles() as $file) {
				self::runCommand('dbal:import', array('file' => $file));
			}
		}
	}
	
	protected static final function importFixture($fixture)
	{
		if (!in_array($fixture, self::$loadedFixtures)) {
			self::runCommand('dbal:import', array('file' => self::$dbInitializer->fixture($fixture)));
			self::$loadedFixtures[] = $fixture;
		}
	}
	
	/**
	 * Override this method, if you need some additional setup.
	 */
	protected static function customSetup()
	{
		
	}

	
	protected static final function runCommand($command, array $options = array())
	{
		$opts = array($command);
		$opts = array_merge($opts, $options);
		self::$application->run(new ArrayInput($opts));
	}
	
	private static function enhance($helpers, array $commands)
	{
		foreach($commands as $cmd) {
			$cmd->setHelperSet($helpers);
		}
		return $commands;
	}
	
	protected function assertDateEquals(DateTime $expected, $actual)
	{
		if(!$actual instanceof DateTime) {
			$this->fail('Actual value '.$actual.' is not an instance of \DateTime!');
		}
		$this->assertEquals($expected->format('Y-m-d'), $actual->format('Y-m-d'));
	}
	
	protected function assertFieldEquals($table, $field, $id, $expectedValue)
	{
		$this->assertEquals($expectedValue, self::$conn->fetchColumn('SELECT `'.$field.'` FROM `'.$table.'` WHERE `id` = '.$id));
	}
	
	protected function assertRelationshipExists($table, $field1, $value1, $field2, $value2)
	{
		$item = self::$conn->fetchAssoc('SELECT `'.$field1.'`, `'.$field2.'` FROM `'.$table.'` WHERE `'.$field1.'` = :f1 AND `'.$field2.'` = :f2', array(':f1' => $value1, ':f2' => $value2));
		$this->assertTrue(!empty($item));
	}
	
	protected function assertRelationshipNotExists($table, $field1, $value1, $field2, $value2)
	{
		$item = self::$conn->fetchAssoc('SELECT `'.$field1.'`, `'.$field2.'` FROM `'.$table.'` WHERE `'.$field1.'` = :f1 AND `'.$field2.'` = :f2', array(':f1' => $value1, ':f2' => $value2));
		$this->assertTrue(empty($item));
	}
	
	protected function initializeField($table, $field, $id, $initialValue)
	{
		self::$conn->executeUpdate('UPDATE `'.$table.'` SET `'.$field.'` = :val WHERE `id` = :id', array(':val' => $initialValue, ':id' => $id));
	}
	
	protected function initializeFieldEx($table, $idName, $id, $field, $initialValue)
	{
		self::$conn->executeUpdate('UPDATE `'.$table.'` SET `'.$field.'` = :val WHERE `'.$idName.'` = :id', array(':val' => $initialValue, ':id' => $id));
	}
	
	protected function assertFieldEqualsEx($table, $idName, $id, $field, $expectedValue)
	{
		$this->assertEquals($expectedValue, self::$conn->fetchColumn('SELECT `'.$field.'` FROM `'.$table.'` WHERE `'.$idName.'` = '.$id));
	}
	
	protected function assertExists($table, $id)
	{
		$item = self::$conn->fetchAssoc('SELECT * FROM `'.$table.'` WHERE `id` = :f1', array(':f1' => $id));
		$this->assertTrue(!empty($item));
	}
	
	protected function assertNotExists($table, $id)
	{
		$item = self::$conn->fetchAssoc('SELECT * FROM `'.$table.'` WHERE `id` = :f1', array(':f1' => $id));
		$this->assertFalse(!empty($item));
	}
}
