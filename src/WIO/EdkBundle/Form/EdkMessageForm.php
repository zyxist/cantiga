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
namespace WIO\EdkBundle\Form;

use Cantiga\Metamodel\Form\EntityTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WIO\EdkBundle\Repository\EdkPublishedDataRepository;

class EdkMessageForm extends AbstractType
{	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefined(['repository']);
		$resolver->setRequired(['repository']);
		$resolver->addAllowedTypes('repository', EdkPublishedDataRepository::class);
		$resolver->setDefaults([
			'translation_domain' => 'public',
			'csrf_protection' => false,
		]);
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('area', ChoiceType::class, [
				'label' => 'Choose an area',
				'choices' => $options['repository']->getFormChoices()
			])
			->add('subject', TextType::class, ['label' => 'Subject'])
			->add('content', TextareaType::class, array('label' => 'Content', 'attr' => ['rows' => 20]))
			->add('authorName', TextType::class, array('label' => 'What is your name?'))
			->add('authorEmail', TextType::class, array('label' => 'Your e-mail', 'required' => false))
			->add('authorPhone', TextType::class, array('label' => 'Your phone number', 'required' => false))
			->add('save', SubmitType::class, array('label' => 'Send message'));
		$builder->get('area')->addModelTransformer(new EntityTransformer($options['repository']));
	}

	public function getName()
	{
		return 'EdkMessage';
	}
}
