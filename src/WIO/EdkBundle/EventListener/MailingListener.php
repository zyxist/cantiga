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
namespace WIO\EdkBundle\EventListener;

use Cantiga\CoreBundle\Mail\MailSenderInterface;
use WIO\EdkBundle\EdkTexts;
use WIO\EdkBundle\Event\RegistrationEvent;

/**
 * Sends the e-mails to the users in response to certain events.
 *
 * @author Tomasz Jędrzejewski
 */
class MailingListener
{
	/**
	 * @var MailSenderInterface 
	 */
	private $mailSender;
	
	public function __construct(MailSenderInterface $mailSender)
	{
		$this->mailSender = $mailSender;
	}
	
	public function onRegistrationCompleted(RegistrationEvent $event)
	{
		$participant = $event->getParticipant();
		$this->mailSender->send(
			EdkTexts::REGISTRATION_MAIL,
			$participant->getEmail(),
			'user \''.$participant->getEmail().'\'',
			[ 'participant' => $participant, 'slug' => $event->getSlug() ]
		);
	}
}
