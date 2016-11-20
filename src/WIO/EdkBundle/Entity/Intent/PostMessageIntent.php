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

namespace WIO\EdkBundle\Entity\Intent;

use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use WIO\EdkBundle\Entity\EdkMessage;
use WIO\EdkBundle\Repository\EdkMessageRepository;

/**
 * Intent of writing a message to the area.
 *
 * @author Tomasz Jędrzejewski
 */
class PostMessageIntent
{
	public $area;
	public $subject;
	public $content;
	public $authorName;
	public $authorEmail;
	public $authorPhone;
	
	private $repository;
	
	public function __construct(EdkMessageRepository $repository)
	{
		$this->repository = $repository;
	}

	public static function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addConstraint(new Callback('validate'));

		$metadata->addPropertyConstraint('subject', new NotBlank());
		$metadata->addPropertyConstraint('subject', new Length(array('min' => 5, 'max' => 100)));
		$metadata->addPropertyConstraint('content', new NotBlank());
		$metadata->addPropertyConstraint('content', new Length(array('min' => 20, 'max' => 3000)));
		$metadata->addPropertyConstraint('authorName', new NotBlank());
		$metadata->addPropertyConstraint('authorName', new Length(array('min' => 5, 'max' => 50)));
		$metadata->addPropertyConstraint('authorEmail', new Length(array('min' => 5, 'max' => 100)));
		$metadata->addPropertyConstraint('authorEmail', new Email());
		$metadata->addPropertyConstraint('authorPhone', new Length(array('min' => 9, 'max' => 30)));
		$metadata->addPropertyConstraint('authorPhone', new Regex(array('pattern' => '/^[0-9\-\+ ]{9,16}$/', 'htmlPattern' => '^[0-9\-\+ ]{9,16}$', 'message' => 'This is not a valid phone number.')));
	}

	public function validate(ExecutionContextInterface $context)
	{
		if (empty($this->authorEmail) && empty($this->authorPhone)) {
			$context->buildViolation('Please specify either e-mail or phone number.')
				->atPath('authorEmail')
				->addViolation();
			$context->buildViolation('Please specify either e-mail or phone number.')
				->atPath('authorPhone')
				->addViolation();
			return false;
		}
		return true;
	}
	
	public function execute()
	{
		$message = new EdkMessage();
		$message->setArea($this->area);
		$message->setSubject($this->subject);
		$message->setContent($this->content);
		$message->setAuthorName($this->authorName);
		$message->setAuthorEmail($this->authorEmail);
		$message->setAuthorPhone($this->authorPhone);
		$message->setIpAddress(ip2long($_SERVER['REMOTE_ADDR']));
		
		$this->repository->insert($message);
	}

}
