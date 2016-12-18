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
namespace Cantiga\CoreBundle\Repository\Utils;

use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;
use Cantiga\CoreBundle\Entity\AreaRequest;
use Cantiga\CoreBundle\Repository\UserAreaRequestRepository;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\UserBundle\Entity\ContactData;
use Cantiga\UserBundle\Repository\ContactRepository;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Stores the form results of area request creation, and initiates the final action.
 */
class AreaRequestFlow
{
	const REQUEST_KEY = 'area-request/request';
	const CONTACT_KEY = 'area-request/contact-data';
	
	/**
	 * @var UserAreaRequestRepository 
	 */
	private $areaRequestRepository;
	/**
	 * @var ContactRepository
	 */
	private $contactRepository;
	
	public function __construct(UserAreaRequestRepository $areaRequestRepository, ContactRepository $contactRepository)
	{
		$this->areaRequestRepository = $areaRequestRepository;
		$this->contactRepository = $contactRepository;
	}
	
	public function clearSession(Session $session)
	{
		$session->remove(self::REQUEST_KEY);
		$session->remove(self::CONTACT_KEY);
	}
	
	public function restoreRequest(Session $session): AreaRequest
	{
		if ($session->has(self::REQUEST_KEY)) {
			return unserialize($session->get(self::REQUEST_KEY));
		} else{
			return new AreaRequest();
		}
	}
	
	public function createContactData(HierarchicalInterface $project, CantigaUserRefInterface $requestor): ContactData
	{
		return $this->contactRepository->findContactData($project, $requestor);
	}
	
	public function persistRequest(Session $session, AreaRequest $request)
	{
		$session->set(self::REQUEST_KEY, serialize($request));
	}
	
	public function persistContactData(Session $session, ContactData $contactData)
	{
		$session->set(self::CONTACT_KEY, serialize($contactData));
	}
	
	public function isCompleted(Session $session): bool
	{
		return $session->has(self::REQUEST_KEY) && $session->has(self::CONTACT_KEY);
	}
	
	public function create(Session $session, HierarchicalInterface $project, CantigaUserRefInterface $requestor): int
	{
		list($areaRequest, $contactData) = $this->unpackObjects($session, $project, $requestor);
		
		$this->clearSession($session);
		
		$this->contactRepository->persistContactData($contactData);
		return $this->areaRequestRepository->insert($areaRequest);
	}
	
	private function unpackObjects(Session $session, HierarchicalInterface $project, CantigaUserRefInterface $requestor)
	{
		$areaRequest = $session->get(self::REQUEST_KEY, null);
		$contactData = $session->get(self::CONTACT_KEY, null);
		
		if (empty($contactData) || empty($areaRequest)) {
			throw new ModelException('Missing data for creating the area request.');
		}
		$areaRequest = unserialize($areaRequest);
		$contactData = unserialize($contactData);

		$areaRequest->setProject($project);
		$areaRequest->setRequestor($requestor);
		
		return [$areaRequest, $contactData];
	}
}
