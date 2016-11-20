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
namespace Cantiga\CoreBundle\Mail;

use Swift_Message;
use Twig_Environment;

/**
 * Wrapper for the Symfony mailing stack that combines it with additional services:
 * the template engine, and the ability to load the e-mail templates from the database. 
 *
 * @author Tomasz Jędrzejewski
 */
class MailSender implements MailSenderInterface
{
	private $mailer;
	/**
	 * @var Twig_Environment
	 */
	private $tpl;
	/**
	 * @var MailLoaderInterface
	 */
	private $mailLoader;
	/**
	 * @var LoggerInterface 
	 */
	private $log;
	/**
	 * @var string
	 */
	private $sourceMail;
	
	public function __construct($mailer, MailLoaderInterface $mailLoader, Twig_Environment $tpl, \Psr\Log\LoggerInterface $log, $sourceMail)
	{
		$this->mailLoader = $mailLoader;
		$this->mailer = $mailer;
		$this->log = $log;
		$this->tpl = $tpl;
		$this->sourceMail = (string) $sourceMail;
	}
	
	public function send($mailTemplate, $recipient, $tag, array $args)
	{
		$subject = $this->mailLoader->getSubject($mailTemplate);
		
		$message = Swift_Message::newInstance()
			->setSubject($subject)
			->setFrom($this->sourceMail)
			->setTo($recipient);
		$message->setBody($this->tpl->render($mailTemplate, $args), 'text/html');
		$this->mailer->send($message);
		$this->log->info('Sent mail message of type \''.$mailTemplate.'\': '.$tag);
	}
}
