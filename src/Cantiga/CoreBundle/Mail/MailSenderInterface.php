<?php
namespace Cantiga\CoreBundle\Mail;

/**
 * @author Tomasz Jędrzejewski
 */
interface MailSenderInterface
{
	/**
	 * Sends the mail message identified by the given <tt>$mailTemplate</tt> to the recipient.
	 * The mail type is used for finding the proper template and subject name, which is
	 * then parsed by Twig template engine and evaluated against the given set of
	 * arguments.
	 * 
	 * @param string $mailTemplate Name of the mail template
	 * @param string $recipient Recipient e-mail address
	 * @param string $tag Tag used to log information about this message
	 * @param array $args Mail template arguments
	 */
	public function send($mailTemplate, $recipient, $tag, array $args);
}
