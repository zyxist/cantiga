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

use Cantiga\CoreBundle\CoreSettings;
use Cantiga\CoreBundle\Settings\ProjectSettings;
use Cantiga\Metamodel\Capabilities\CompletenessCalculatorInterface;
use Cantiga\Metamodel\CustomForm\CustomFormEventSubscriber;
use Cantiga\Metamodel\CustomForm\CustomFormModelInterface;
use Cantiga\Metamodel\Form\EntityTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AreaProfileForm extends AbstractType
{	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefined(['customFormModel', 'territoryRepository', 'projectSettings']);
		$resolver->setRequired(['customFormModel', 'territoryRepository', 'projectSettings']);
		$resolver->addAllowedTypes('customFormModel', CustomFormModelInterface::class);
		$resolver->addAllowedTypes('projectSettings', ProjectSettings::class);
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$hint = $options['projectSettings']->get(CoreSettings::AREA_NAME_HINT)->getValue();
		
		$builder
			->add('name', TextType::class, array('label' => 'Area name', 'attr' => ['help_text' => $hint]))
			->add('territory', ChoiceType::class, array('label' => 'Territory', 'choices' => $options['territoryRepository']->getFormChoices()))
			->add('save', SubmitType::class, array('label' => 'Save'));
		$builder->get('territory')->addModelTransformer(new EntityTransformer($options['territoryRepository']));
		$builder->addEventSubscriber(new CustomFormEventSubscriber($options['customFormModel']));
		$builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
			if ($options['customFormModel'] instanceof CompletenessCalculatorInterface) {
				$entity = $event->getData();
				$entity->setPercentCompleteness($options['customFormModel']->calculateCompleteness($entity->getCustomData()));
			}
		});
	}

	public function getName()
	{
		return 'AreaProfile';
	}
}