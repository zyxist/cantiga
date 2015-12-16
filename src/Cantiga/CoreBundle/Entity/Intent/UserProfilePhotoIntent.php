<?php
namespace Cantiga\CoreBundle\Entity\Intent;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Cantiga\CoreBundle\Entity\PhotoFormatter;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\CoreBundle\Repository\ProfileRepository;

/**
 * Description of UserProfilePhotoIntent
 *
 * @author Tomasz Jędrzejewski
 */
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
