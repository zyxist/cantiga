<?php
namespace Cantiga\CoreBundle\Mail;

use Swift_Message;
use Twig_Environment;

/**
 * Description of MailSender
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
