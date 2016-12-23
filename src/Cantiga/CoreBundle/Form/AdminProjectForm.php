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
namespace Cantiga\CoreBundle\Form;

use Cantiga\CoreBundle\Api\Modules;
use Cantiga\CoreBundle\Form\Type\BooleanType;
use Cantiga\CoreBundle\Repository\ArchivedProjectRepository;
use Cantiga\Metamodel\Form\EntityTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminProjectForm extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefined('projectRepo');
		$resolver->setRequired('projectRepo');
		$resolver->addAllowedTypes('projectRepo', ArchivedProjectRepository::class);
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', TextType::class, array('label' => 'Name'))
			->add('description', TextareaType::class, array('label' => 'Description'))
			->add('parentProject', ChoiceType::class, array('label' => 'Parent project', 'required' => false, 'choice_translation_domain' => false, 'choices' => $options['projectRepo']->getFormChoices()))
			->add('modules', ChoiceType::class, array('label' => 'Modules', 'expanded' => true, 'multiple' => true, 'choices' => Modules::getFormEntries()))
			->add('areasAllowed', BooleanType::class, array('label' => 'Areas allowed?'))
			->add('areaRegistrationAllowed', BooleanType::class, array('label' => 'Area registration allowed?'))
			->add('save', SubmitType::class, array('label' => 'Save'));
		
		$builder->get('parentProject')->addModelTransformer(new EntityTransformer($options['projectRepo']));
	}

	public function getName()
	{
		return 'Project';
	}
}