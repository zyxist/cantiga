<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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
namespace Cantiga\CoreBundle\Form;

use Cantiga\Metamodel\CustomForm\CustomFormEventSubscriber;
use Cantiga\Metamodel\CustomForm\CustomFormModelInterface;
use Cantiga\Metamodel\Form\EntityTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class GroupAreaForm extends AbstractType
{
	/**
	 * @var CustomFormModelInterface
	 */
	private $customFormModel;
	private $territoryRepository;
	private $statusRepository;
	
	public function __construct(CustomFormModelInterface $customFormModel, $territoryRepository, $statusRepository)
	{
		$this->customFormModel = $customFormModel;
		$this->territoryRepository = $territoryRepository;
		$this->statusRepository = $statusRepository;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', 'text', array('label' => 'Name'))
			->add('territory', 'choice', array('label' => 'Territory', 'choices' => $this->territoryRepository->getFormChoices()))
			->add('status', 'choice', array('label' => 'Status', 'choices' => $this->statusRepository->getFormChoices()))
			->add('save', 'submit', array('label' => 'Save'));
		$builder->get('territory')->addModelTransformer(new EntityTransformer($this->territoryRepository));
		$builder->get('status')->addModelTransformer(new EntityTransformer($this->statusRepository));
		$builder->addEventSubscriber(new CustomFormEventSubscriber($this->customFormModel));
	}

	public function getName()
	{
		return 'Area';
	}
}