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
namespace Cantiga\MilestoneBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Tomasz Jędrzejewski
 */
class UpdateStatusCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('cantiga:milestone:update-status')
			->setDescription('Updates the status of areas, using milestone status rules.')
		;
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$repository = $this->getContainer()->get('cantiga.milestone.repo.update');
		foreach ($repository->findUpdatableProjects() as $project) {
			$output->write('Running status update for project \''.$project['name'].'\': ');
			$count = $repository->runUpdateForProject($project['id']);
			$output->writeln($count.' areas updated');
		}
	}
}