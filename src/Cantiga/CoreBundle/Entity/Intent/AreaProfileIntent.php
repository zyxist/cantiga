<?php
namespace Cantiga\CoreBundle\Entity\Intent;

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Repository\ProjectAreaRepository;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Intent for editing the profile by the area member.
 *
 * @author Tomasz Jędrzejewski
 */
class AreaProfileIntent
{
	public $name;
	public $territory;
	public $customData;
	
	/**
	 * @var Area
	 */
	private $area;
	/**
	 * @var ProjectAreaRepository 
	 */
	private $repository;
	
	public function __construct(Area $area, ProjectAreaRepository $repository)
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
	
	public function execute()
	{
		$this->area->setName($this->name);
		$this->area->setTerritory($this->territory);
		$this->area->setCustomData($this->customData);
		$this->repository->update($this->area);
	}
}
