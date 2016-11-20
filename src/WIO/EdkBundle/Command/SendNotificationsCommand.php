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
namespace WIO\EdkBundle\Command;

use Cantiga\CoreBundle\Mail\MailSenderInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WIO\EdkBundle\EdkTexts;

/**
 * @author Tomasz Jędrzejewski
 */
class SendNotificationsCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('cantiga:edk:send-notifications')
			->setDescription('Sends the e-mail notifications about the area status.')
		;
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$repository = $this->getContainer()->get('wio.edk.repo.export');
		$mailer = $this->getContainer()->get('cantiga.mail.sender');
		$summary = $repository->prepareMailSummary();
		
		foreach ($summary as $area) {
			try {
				$this->distributeNotification($mailer, $area);
				$output->writeln('<info>OK</info> '.$area['name'].': notification sent');
			} catch(Exception $exception) {
				$output->writeln('<error>ERROR</error> '.$area['name'].': '.$exception->getMessage());
			}
		}
	}
	
	private function distributeNotification(MailSenderInterface $mailer, $area)
	{
		if (sizeof($area['emails']) > 0) {
			$mailer->send(EdkTexts::NOTIFICATION_MAIL.'@@'.$area['locale'], $area['emails'], $area['id'], $area);
		}
	}
}