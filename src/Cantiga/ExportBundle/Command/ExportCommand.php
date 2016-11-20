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
namespace Cantiga\ExportBundle\Command;

use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends ContainerAwareCommand
{
	const ENCRYPTION_ALGORITHM = 'aes-256-cbc';
	
	protected function configure()
	{
		$this
			->setName('cantiga:export-data')
			->setDescription('Exports the data to the external services via REST.')
		;
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$exportEngine = $this->getContainer()->get('cantiga.export.repo.engine');
		foreach ($exportEngine->findActiveExports() as $export) {
			$output->writeln('<info>Exporting data to \''.$export['name'].'\' endpoint.</info>');
			try {
				$result = $exportEngine->exportData($export, function($text) use($output) {
					$output->writeln($text, OutputInterface::VERBOSITY_NORMAL);
				});
				if ($this->send($export, $this->encrypt($export, $result), $output)) {
					$output->writeln('<info>Export completed</info>');
				} else {
					$output->writeln('<info>Export failed</info>');
				}
			} catch(Exception $exception) {
				$output->writeln('<error>Export failed: '.$exception->getMessage().'</error>');
			}
		}
	}
	
	private function encrypt($export, $output)
	{
		$key = base64_decode($export['encryptionKey']);
		
		if (strlen($key) != 32) {
			throw new \RuntimeException('The key must have the length of 32 bytes!');
		}
		
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::ENCRYPTION_ALGORITHM));
		return base64_encode($iv.openssl_encrypt(json_encode($output), self::ENCRYPTION_ALGORITHM, $key, true, $iv));
	}
	
	private function send($export, $encrypted, OutputInterface $output)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $export['url']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encrypted); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));
		
		$result = curl_exec($ch);
		$errno = curl_errno($ch);
		if ($errno !== 0) {
			$output->writeln('<error>The server responded with error: '.$errno.' ('.curl_error($ch).')</error>');
			curl_close($ch);
			return false;
		}
		curl_close($ch);
		return true;
	}
}