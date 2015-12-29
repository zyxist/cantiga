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

use Cantiga\CoreBundle\CoreSettings;
use Cantiga\CoreBundle\Settings\ProjectSettings;
use Cantiga\Metamodel\CustomForm\CustomFormEventSubscriber;
use Cantiga\Metamodel\CustomForm\CustomFormModelInterface;
use Cantiga\Metamodel\Form\EntityTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AreaProfileForm extends AbstractType
{
	/**
	 * @var ProjectSettings 
	 */
	private $projectSettings;
	/**
	 * @var CustomFormModelInterface
	 */
	private $customFormModel;
	private $territoryRepository;
	
	public function __construct(ProjectSettings $settings, CustomFormModelInterface $customFormModel, $territoryRepository)
	{
		$this->customFormModel = $customFormModel;
		$this->territoryRepository = $territoryRepository;
		$this->projectSettings = $settings;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$hint = $this->projectSettings->get(CoreSettings::AREA_NAME_HINT)->getValue();
		
		$builder
			->add('name', 'text', array('label' => 'Area name', 'attr' => ['help_text' => $hint]))
			->add('territory', 'choice', array('label' => 'Territory', 'choices' => $this->territoryRepository->getFormChoices()))
			->add('save', 'submit', array('label' => 'Save'));
		$builder->get('territory')->addModelTransformer(new EntityTransformer($this->territoryRepository));
		$builder->addEventSubscriber(new CustomFormEventSubscriber($this->customFormModel));
	}

	public function getName()
	{
		return 'AreaProfile';
	}
}