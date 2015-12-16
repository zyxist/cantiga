<?php

namespace Cantiga\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cantiga\CoreBundle\Generator\CrudGenerator;
use Cantiga\CoreBundle\Generator\ReportInterface;

/**
 * Description of GenerateEntity
 *
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
