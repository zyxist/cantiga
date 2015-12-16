<?php
namespace Cantiga\CoreBundle\Mail;

use Twig_LoaderInterface;

/**
 * @author Tomasz Jędrzejewski
 */
interface MailLoaderInterface extends Twig_LoaderInterface
{
	/**
	 * Returns the subject for the given mail
	 * 
	 * @param string $mailTemplate Mail template ID
	 * @return string
	 */
	public function getSubject($mailTemplate);
}
