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
namespace Cantiga\CoreBundle\Services;

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\Components\Hierarchy\Importer\ImporterInterface;
use Cantiga\Components\Hierarchy\MembershipStorageInterface;
use Cantiga\Components\Hierarchy\PlaceLoaderInterface;
use Cantiga\CoreBundle\Api\Actions\QuestionHelper;
use Cantiga\CoreBundle\Entity\Project;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ImporterService implements ImporterInterface
{
	private $membershipStorage;
	private $tokenStorage;
	/**
	 * @var TranslatorInterface 
	 */
	private $translator;
	/**
	 * @var array
	 */
	private $loaders = [];
	/**
	 * @var array
	 */
	private $cache;
	
	public function __construct(MembershipStorageInterface $membershipStorage, TokenStorageInterface $tokenStorage, TranslatorInterface $translator)
	{
		$this->membershipStorage = $membershipStorage;
		$this->tokenStorage = $tokenStorage;
		$this->translator = $translator;
	}
	
	public function addPlaceLoader(string $type, PlaceLoaderInterface $loader)
	{
		$this->loaders[$type] = $loader;
	}
	
	public function getImportLabel(): string
	{
		if ($this->membershipStorage->hasMembership()) {
			$place = $this->loadMatchingPlace($this->membershipStorage->getMembership());
			if (false !== $place) {
				$project = $this->getProject($place);
				return $this->translator->trans('Import from 0', [$project->getName()], 'general');
			}
		}
		return '';
	}

	public function isImportAvailable(): bool
	{
		if ($this->membershipStorage->hasMembership()) {
			$place = $this->loadMatchingPlace($this->membershipStorage->getMembership());
			return false !== $place;
		}
		return false;
	}
	
	public function getImportSource(): HierarchicalInterface
	{
		return $this->loadMatchingPlace($this->membershipStorage->getMembership());
	}

	public function getImportDestination(): HierarchicalInterface
	{
		return $this->membershipStorage->getMembership()->getPlace();
	}

	public function getImportQuestion(string $pageTitle, string $question): QuestionHelper
	{
		$helper = new QuestionHelper($this->translator->trans($question));
		$helper->title($pageTitle, $this->getImportLabel());
		return $helper;
	}
	
	private function getProject(HierarchicalInterface $place): Project
	{
		if ($place->isRoot()) {
			return $place;
		} else {
			return $place->getRootElement();
		}
	}

	private function loadMatchingPlace(Membership $membership)
	{
		$place = $membership->getPlace();
		$key = $place->getTypeName().':'.$place->getId();
		if (isset($this->cache[$key])) {
			return $this->cache[$key];
		}
		
		if (!isset($this->loaders[$place->getTypeName()])) {
			throw new LogicException('Place loader not registered for place type \''.$place->getTypeName().'\'');
		}
		
		$loader = $this->loaders[$place->getTypeName()];
		return $this->cache[$key] = $loader->loadPlaceForImport($membership->getPlace(), $this->tokenStorage->getToken()->getUser());
	}
}