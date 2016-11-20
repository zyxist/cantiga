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

/**
 * Represents an implementation of the wrapper for the Symfony mailing stack.
 * 
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
