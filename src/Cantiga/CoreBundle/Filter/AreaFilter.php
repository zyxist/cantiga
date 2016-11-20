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

use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Repository\ProjectAreaStatusRepository;
use Cantiga\CoreBundle\Repository\ProjectGroupCategoryRepository;
use Cantiga\CoreBundle\Repository\ProjectGroupRepository;
use Cantiga\CoreBundle\Repository\ProjectTerritoryRepository;
use Cantiga\Metamodel\DataFilterInterface;
use Cantiga\Metamodel\Form\EntityTransformer;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\QueryOperator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Generic filter that allows filtering the data through areas.
 *
 * @author Tomasz Jędrzejewski
 */
class AreaFilter implements DataFilterInterface
{
	private $status;
	private $group;
	private $category;
	private $territory;
	
	private $fixedGroup = false;
	
	private $statusRepository;
	private $groupRepository;
	private $categoryRepository;
	private $territoryRepository;
	
	public function __construct(ProjectAreaStatusRepository $areaStatusRepository, ProjectGroupRepository $groupRepository, ProjectGroupCategoryRepository $categoryRepository, ProjectTerritoryRepository $territoryRepository)
	{
		$this->statusRepository = $areaStatusRepository;
		$this->groupRepository = $groupRepository;
		$this->categoryRepository = $categoryRepository;
		$this->territoryRepository = $territoryRepository;
	}
	
	public function setTargetProject(Project $project)
	{
		$this->statusRepository->setProject($project);
		$this->groupRepository->setProject($project);
		$this->categoryRepository->setProject($project);
		$this->territoryRepository->setProject($project);
	}
		
	public function getStatus()
	{
		return $this->status;
	}

	public function getGroup()
	{
		return $this->group;
	}

	public function getCategory()
	{
		return $this->category;
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

	public function setGroup($group)
	{
		$this->group = $group;
		return $this;
	}

	public function setCategory($category)
	{
		$this->category = $category;
		return $this;
	}

	public function setTerritory($territory)
	{
		$this->territory = $territory;
		return $this;
	}
	
	/**
	 * Checks if we can change the groups and categories (false) or not (true).
	 * @return boolean
	 */
	public function isFixedGroup()
	{
		return $this->fixedGroup;
	}
	
	/**
	 * Fixes the filter on the given group - the user can no longer change groups and categories.
	 * 
	 * @param Group $group
	 * @return AreaFilter
	 */
	public function fixedGroup()
	{
		$this->fixedGroup = true;
		return $this;
	}
	
	public function isCategorySelected()
	{
		return null !== $this->category;
	}
	
	public function createForm(FormBuilderInterface $formBuilder)
	{
		$formBuilder->setMethod('GET');
		$formBuilder->add('status', ChoiceType::class, ['label' => 'Status', 'choices' => $this->statusRepository->getFormChoices(), 'required' => false]);
		if (!$this->fixedGroup) {
			$formBuilder->add('group', ChoiceType::class, ['label' => 'Group', 'choices' => $this->groupRepository->getFormChoices(), 'required' => false]);
			$formBuilder->add('category', ChoiceType::class, ['label' => 'Category', 'choices' => $this->categoryRepository->getFormChoices(), 'required' => false]);
		}
		$formBuilder->add('territory', ChoiceType::class, ['label' => 'Territory', 'choices' => $this->territoryRepository->getFormChoices(), 'required' => false]);
		$formBuilder->add('submit', SubmitType::class, ['label' => 'Filter']);
		
		$formBuilder->get('status')->addModelTransformer(new EntityTransformer($this->statusRepository));
		if (!$this->fixedGroup) {
			$formBuilder->get('group')->addModelTransformer(new EntityTransformer($this->groupRepository));
			$formBuilder->get('category')->addModelTransformer(new EntityTransformer($this->categoryRepository));
		}
		$formBuilder->get('territory')->addModelTransformer(new EntityTransformer($this->territoryRepository));
		
		return $formBuilder->getForm();
	}
	
	public function createFilterClause()
	{
		$op = QueryOperator::op(' AND ');
		if (null !== $this->status) {
			$op->expr(QueryClause::clause('i.statusId = :statusId', ':statusId', $this->status->getId()));
		}
		if (null !== $this->group) {
			$op->expr(QueryClause::clause('i.groupId = :groupId', ':groupId', $this->group->getId()));
		}
		if (null !== $this->category) {
			$op->expr(QueryClause::clause('g.categoryId = :categoryId', ':categoryId', $this->category->getId()));
		}
		if (null !== $this->territory) {
			$op->expr(QueryClause::clause('i.territoryId = :territoryId', ':territoryId', $this->territory->getId()));
		}
		return $op;
	}
	
	public function createParamArray()
	{
		$result = [];
		if (!$this->fixedGroup) {
			if (null !== $this->category) {
				$result['category'] = $this->category->getId();
			}
			if (null !== $this->group) {
				$result['group'] = $this->group->getId();
			}
		}
		if (null !== $this->territory) {
			$result['territory'] = $this->territory->getId();
		}
		if (null !== $this->status) {
			$result['status'] = $this->status->getId();
		}
		return ['form' => $result];
	}
}
