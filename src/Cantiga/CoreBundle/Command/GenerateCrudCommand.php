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
namespace Cantiga\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cantiga\CoreBundle\Generator\CrudGenerator;
use Cantiga\CoreBundle\Generator\ReportInterface;

/**
 * @author Tomasz Jędrzejewski
 */
class GenerateCrudCommand extends ContainerAwareCommand implements ReportInterface
{
	private $output;

	protected function configure()
	{
		$this
			->setName('cantiga:generate-crud')
			->setDescription('Code generator: generates a new CRUD panel.')
			->addArgument(
				'location', InputArgument::REQUIRED, 'Root location of the bundle'
			)
			->addArgument(
				'controller', InputArgument::REQUIRED, 'Controller name'
			)
			->addArgument(
				'entity', InputArgument::REQUIRED, 'Entity name (capitalized)'
			)
			->addArgument(
				'repositoryService', InputArgument::REQUIRED, 'Repository service'
			)
			->addArgument(
				'routePrefix', InputArgument::REQUIRED, 'Route prefix'
			)
			->addArgument(
				'baseController', InputArgument::REQUIRED, 'Base controller'
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->output = $output;
		$namespace = trim(str_replace('/', '\\', $input->getArgument('location')), '\\');
		$location = $input->getArgument('location');
		$entityName = ucfirst($input->getArgument('entity'));
		$controller = $input->getArgument('controller');
		$routePrefix = $input->getArgument('routePrefix');
		$service = $input->getArgument('repositoryService');
		$baseController = $input->getArgument('baseController');
		$rootDir = $this->getContainer()->get('kernel')->getRootDir();

		if (!is_dir($rootDir.'/../src/'.$location)) {
			$output->writeln('<error>Invalid bundle location: '.$rootDir.'/../src/'.$location.'</error>');
			return;
		}
		
		$generator = new CrudGenerator($this, $controller, $baseController, $routePrefix, $entityName, $service);
		$generator->setNamespace($namespace);
		$generator->setLocation($rootDir.'/../src/'.$location);
		$generator->generate();
		$this->output->writeln('<info>Done</info>');
	}

	public function reportStatus($status)
	{
		$this->output->writeln($status);
	}
}
