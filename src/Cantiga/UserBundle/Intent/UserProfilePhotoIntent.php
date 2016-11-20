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
namespace Cantiga\UserBundle\Intent;

use Cantiga\CoreBundle\Entity\PhotoFormatter;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\UserBundle\Repository\ProfileRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserProfilePhotoIntent
{
	const MINIMUM_SIZE = 100;
	const MAXIMUM_SIZE = 700;
	const START_SIZE = 128;
	
	/**
	 * @var UploadedFile
	 */
	public $photo;
	/**
	 * @var User
	 */
	private $user;
	/**
	 * @var ProfileRepository
	 */
	private $repository;
	private $output;
	
	public function __construct(User $user, ProfileRepository $repository, $output)
	{
		$this->user = $user;
		$this->repository = $repository;
		$this->output = $output;
	}
	
	public function execute()
	{
		if ($this->photo->isValid()) {
			$name = sha1($this->user->getId().$this->user->getLogin().$this->user->getRegisteredAt().time().uniqid('ffdf'));
			
			$photoUploader = new PhotoFormatter($this->photo->getRealPath(), self::MINIMUM_SIZE, self::MAXIMUM_SIZE, $this->output);
			
			$old = $this->user->getAvatar();
			$photoUploader->setNewName($name);
			$photoUploader->loadAndScale(self::START_SIZE);
			
			$this->user->setAvatar($name);
			$this->repository->update($this->user);
			if (!empty($old)) {
				$photoUploader->removeOld($old, self::START_SIZE);
			}
		}
	}
}
