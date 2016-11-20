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
namespace Cantiga\CoreBundle\Filter;

use Cantiga\CoreBundle\Entity\AreaRequest;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Repository\ProjectTerritoryRepository;
use Cantiga\Metamodel\DataFilterInterface;
use Cantiga\Metamodel\Form\EntityTransformer;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\QueryOperator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Filter for the area request list.
 *
 * @author Tomasz Jędrzejewski
 */
class AreaRequestFilter implements DataFilterInterface
{
	private $status;
	private $territory;
	private $territoryRepository;
	private $translator;
	
	public function __construct(TranslatorInterface $translator, ProjectTerritoryRepository $territoryRepository)
	{
		$this->translator = $translator;
		$this->territoryRepository = $territoryRepository;
	}
	
	public function setTargetProject(Project $project)
	{
		$this->territoryRepository->setProject($project);
	}
		
	public function getStatus()
	{
		return $this->status;
	}

	public function getTerritory()
	{
		return $this->territory;
	}

	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	public function setTerritory($territory)
	{
		$this->territory = $territory;
		return $this;
	}
	
	public function createForm(FormBuilderInterface $formBuilder)
	{
		$formBuilder->setMethod('GET');
		$formBuilder->add('status', ChoiceType::class, ['label' => 'Status', 'choices' => $this->translateStatus(AreaRequest::statusList()), 'required' => false]);
		$formBuilder->add('territory', ChoiceType::class, ['label' => 'Territory', 'choices' => $this->territoryRepository->getFormChoices(), 'required' => false]);
		$formBuilder->add('submit', SubmitType::class, ['label' => 'Filter']);
		$formBuilder->get('territory')->addModelTransformer(new EntityTransformer($this->territoryRepository));
		
		return $formBuilder->getForm();
	}
	
	public function createFilterClause()
	{
		$op = QueryOperator::op(' AND ');
		if (null !== $this->status) {
			$op->expr(QueryClause::clause('i.status = :status', ':status', $this->status));
		}
		if (null !== $this->territory) {
			$op->expr(QueryClause::clause('i.territoryId = :territoryId', ':territoryId', $this->territory->getId()));
		}
		return $op;
	}
	
	public function createParamArray()
	{
		$result = [];
		if (null !== $this->territory) {
			$result['territory'] = $this->territory->getId();
		}
		if (null !== $this->status) {
			$result['status'] = $this->status;
		}
		return ['form' => $result];
	}
	
	private function translateStatus(array $status)
	{
		foreach ($status as &$str) {
			$str = $this->translator->trans($str, [], 'statuses');
		}
		return $status;
	}
}
