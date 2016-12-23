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
namespace Cantiga\MilestoneBundle\Form;

use Cantiga\Metamodel\Form\EntityTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MilestoneStatusRuleForm extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefined(['statusRepository', 'milestoneRepository']);
		$resolver->setRequired(['statusRepository', 'milestoneRepository']);
		
		$resolver->setDefaults(array(
			'translation_domain' => 'milestone'
		));
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$statusChoices = $options['statusRepository']->getFormChoices();
		$milestoneChoices = $options['milestoneRepository']->getFormChoices('Area');
		
		$builder
			->add('name', TextType::class, ['label' => 'Name', 'translation_domain' => 'general'])
			->add('newStatus', ChoiceType::class, array('label' => 'New status', 'required' => true, 'choices' => $statusChoices))
			->add('prevStatus', ChoiceType::class, array('label' => 'Previous status', 'required' => true, 'choices' => $statusChoices))
			->add('milestoneMap', ChoiceType::class, array('label' => 'Required milestones', 'expanded' => true, 'multiple' => true, 'choices' => $milestoneChoices))
			->add('activationOrder', NumberType::class, array('label' => 'Activation order'))
			->add('save', SubmitType::class, ['label' => 'Save', 'translation_domain' => 'general']);
		
		$builder->get('newStatus')->addModelTransformer(new EntityTransformer($options['statusRepository']));
		$builder->get('prevStatus')->addModelTransformer(new EntityTransformer($options['statusRepository']));
	}
	
	public function getName()
	{
		return 'MilestoneStatusRule';
	}
}
