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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Cantiga\Metamodel\Exception\ModelException;

/**
 * Command for archivizing the projects. Because this is a potentially destructive operation for data,
 * we do not allow to perform it directly from the panel.
 *
 * @author Tomasz Jędrzejewski
 */
class ArchivizeProjectCommand extends ContainerAwareCommand
{

	protected function configure()
	{
		$this
			->setName('cantiga:project:archivize')
			->setDescription('Archivizes the given project.')
			->addArgument('project-id', InputArgument::REQUIRED, 'ID of the project to archivize');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		try {
			$repository = $this->getContainer()->get('cantiga.core.repo.project');
			$project = $repository->getItem($input->getArgument('project-id'));
			$helper = $this->getHelper('question');

			$question = new ConfirmationQuestion("Do you really want to archivize the project '".$project->getName()."'? (yes/no) ", false);
			$answer = $helper->ask($input, $output, $question);

			if ($answer) {
				$project->archivize();
				$repository->update($project);
				$output->writeln('<info>The project \''.$project->getName().'\' has been archivized.</info>');
			} else {
				$output->writeln('<info>Aborting...</info>');
			}
		} catch(ModelException $exception) {
			$output->writeln('<error>'.$exception->getMessage().'</error>');
		} finally {
			$this->getContainer()->get('cantiga.transaction')->closeTransaction();
		}
	}
}
