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
namespace Cantiga\CoreBundle\Entity\Intent;

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Repository\AreaMgmtRepository;
use Cantiga\CoreBundle\Repository\ProjectAreaRepository;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Intent for editing the profile by the area member.
 */
class AreaProfileIntent
{
	public $name;
	public $territory;
	public $customData;
	private $percentCompleteness;
	
	/**
	 * @var Area
	 */
	private $area;
	/**
	 * @var ProjectAreaRepository 
	 */
	private $repository;
	
	public function __construct(Area $area, AreaMgmtRepository $repository)
	{
		$this->area = $area;
		$this->repository = $repository;
		
		$this->name = $this->area->getName();
		$this->territory = $this->area->getTerritory();
		$this->customData = $this->area->getCustomData();
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata) {
		$metadata->addPropertyConstraint('name', new NotBlank());
		$metadata->addPropertyConstraint('name', new Length(array('min' => 2, 'max' => 100)));
	}
	
	public function getCustomData()
	{
		return $this->customData;
	}
	
	public function setPercentCompleteness($percent)
	{
		$this->percentCompleteness = $percent;
	}
	
	public function execute()
	{
		$this->area->setName($this->name);
		$this->area->setTerritory($this->territory);
		$this->area->setPercentCompleteness($this->percentCompleteness);
		$this->area->setCustomData($this->customData);
		$this->repository->update($this->area);
	}
}
