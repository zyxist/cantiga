<?php

namespace Cantiga\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cantiga\CoreBundle\Generator\EntityGenerator;
use Cantiga\CoreBundle\Generator\ReportInterface;

/**
 * Description of GenerateEntity
 *
 * @author Tomasz Jędrzejewski
 */
class GenerateEntityCommand extends ContainerAwareCommand implements ReportInterface
{
	private $output;

	protected function configure()
	{
		$this
			->setName('cantiga:generate-entity')
			->setDescription('Code generator: generates a new entity.')
			->addArgument(
				'location', InputArgument::REQUIRED, 'Root location of the bundle'
			)
			->addArgument(
				'entity', InputArgument::REQUIRED, 'Entity name (capitalized)'
			)
			->addArgument(
				'service', InputArgument::REQUIRED, 'Name of the repository service'
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->output = $output;
		$namespace = trim(str_replace('/', '\\', $input->getArgument('location')), '\\');
		$location = $input->getArgument('location');
		$entityName = ucfirst($input->getArgument('entity'));
		$service = $input->getArgument('service');
		$rootDir = $this->getContainer()->get('kernel')->getRootDir();

		if (!is_dir($rootDir.'/../src/'.$location)) {
			$output->writeln('<error>Invalid bundle location: '.$rootDir.'/../src/'.$location.'</error>');
			return;
		}
		
		$generator = new EntityGenerator($this, $entityName, $service);
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
