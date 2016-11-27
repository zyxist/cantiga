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

namespace Cantiga\CoreBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Connection as Connection2;
use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\DBAL\DriverManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;

/**
 * Creates a database from the schema files.
 */
class InstallDatabaseCommand extends ContainerAwareCommand
{
	private $dbname;

	protected function configure()
	{
		$this
			->setName('cantiga:install:db')
			->setDescription('Installs a database schema for the specified environment. See help for example usages.')
			->setHelp('Installs a Cantiga database schema, using connection data for the specified environment. Examples:'.PHP_EOL
				. ' php bin/console cantiga:install:db --env=prod -i --type=mysql    Installs a production DB with default data.'.PHP_EOL
				. ' php bin/console cantiga:install:db --env=dev -i --type=mysql     Installs a development DB with default data.'.PHP_EOL
				. ' php bin/console cantiga:install:db --env=test -d --type=mysql    Recreates a database for automated tests (no default data).'.PHP_EOL)
			->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Database type (required, supported values: mysql)')
			->addOption('drop', 'd', InputOption::VALUE_NONE, 'Drops an existing database schema.')
			->addOption('init', 'i', InputOption::VALUE_NONE, 'Initializes the database with default data.');
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$conn = $this->getConnection();
		
		if ($input->getOption('drop')) {
			$this->dropExistingDatabase($conn, $output);
		}
		$this->createNewDatabase($conn, $output);
		
		$dbAwareConnection = $this->getConnection(true);
		$this->importDatabaseStructure($dbAwareConnection, $input->getOption('type'), $output);
		if ($input->getOption('init')) {
			$this->importInitialData($dbAwareConnection, $input->getOption('type'), $output);
		}
	}

	private function getConnection(bool $dbAware = false): Connection
	{
		$connection = $this->getContainer()->get('doctrine')->getConnection(null);
		$params = $connection->getParams();
		if (isset($params['master'])) {
			$params = $params['master'];
		}

		if (!$dbAware) {
			$this->dbname = isset($params['path']) ? $params['path'] : (isset($params['dbname']) ? $params['dbname'] : false);
			if (!$this->dbname) {
				throw new \InvalidArgumentException("Connection does not contain a 'path' or 'dbname' parameter and cannot be dropped.");
			}
			unset($params['dbname']);
		}
		$connection->close();
		return DriverManager::getConnection($params);
	}
	
	private function dropExistingDatabase(Connection $conn, OutputInterface $output)
	{
		$shouldDropDatabase = in_array($this->dbname, $conn->getSchemaManager()->listDatabases());
		if ($shouldDropDatabase) {
			$output->writeln('<info>Dropping existing database '.$this->dbname.'</info>');
			$conn->getSchemaManager()->dropDatabase($this->dbname);
		} else {
			$output->writeln('<info>The database '.$this->dbname.' does not exist.</info>');
		}
	}
	
	private function createNewDatabase(Connection $conn, OutputInterface $output)
	{
		if (in_array($this->dbname, $conn->getSchemaManager()->listDatabases())) {
			throw new \RuntimeException('The database '.$this->dbname.' already exists!');
		}
		$output->writeln('<info>Creating a new database '.$this->dbname.'</info>');
		$conn->getSchemaManager()->createDatabase($this->dbname);
	}
	
	private function importDatabaseStructure(Connection $conn, $databaseType, OutputInterface $output)
	{
		$output->writeln('<info>Importing the database structure.</info>');
		
		$dbFilePath = $this->getContainer()->getParameter('db_files');
		$structureFile = realpath($dbFilePath.$databaseType.DIRECTORY_SEPARATOR.'fresh-structure.sql');
		$dataFile = $dbFilePath.$databaseType.DIRECTORY_SEPARATOR.'fresh-data.sql';
		
		$output->writeln('   Executing: '.$structureFile);
		$this->executeSqlFile($conn->getWrappedConnection(), $structureFile, $output);
	}
	
	private function importInitialData(Connection $conn, $databaseType, OutputInterface $output)
	{
		$output->writeln('<info>Importing the initial data.</info>');
		
		$dbFilePath = $this->getContainer()->getParameter('db_files');
		$dataFile = realpath($dbFilePath.$databaseType.DIRECTORY_SEPARATOR.'fresh-data.sql');
		
		$output->writeln('   Executing: '.$dataFile);
		$this->executeSqlFile($conn->getWrappedConnection(), $dataFile, $output);
	}

	private function executeSqlFile(Connection2 $conn, $filePath, OutputInterface $output)
	{
		$sql = file_get_contents($filePath);

		if ($conn instanceof PDOConnection) {
			$statementCount = 0;
			try {
				$startTime = microtime(true);
				$stmt = $conn->prepare($sql);
				$stmt->execute();
				
				while ($stmt->nextRowset()) {
					$statementCount++;
				}
				$totalTime = (microtime(true) - $startTime) * 1000;
				
				$output->write(sprintf('   <comment>%d statements executed in %d ms</comment>', $statementCount, $totalTime) . PHP_EOL);
			} catch (\PDOException $e) {
				$output->write('   <error>Error in statement nr '. $statementCount .'</error>' . PHP_EOL);
				throw $e;
			} finally {
				if (!empty($stmt)) {
					$stmt->closeCursor();
				}
			}
		} else {
			throw new \RuntimeException('Non-PDO database drivers are not supported right now.');
		}
	}
}
