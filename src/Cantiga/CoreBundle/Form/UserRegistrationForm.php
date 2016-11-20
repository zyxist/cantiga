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

use Cantiga\CoreBundle\Repository\LanguageRepository;
use Cantiga\Metamodel\Form\EntityTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRegistrationForm extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefined(['languageRepository']);
		$resolver->setRequired(['languageRepository']);
		$resolver->addAllowedTypes('languageRepository', LanguageRepository::class);
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', TextType::class, array('label' => 'Full name', 'attr' => ['placeholder' => 'Full name']))
			->add('login', TextType::class, array('label' => 'Login', 'attr' => ['placeholder' => 'Login']))
			->add('password', PasswordType::class, array('label' => 'Password', 'attr' => ['placeholder' => 'Password']))
			->add('repeatPassword', PasswordType::class, array('label' => 'Repeat password', 'attr' => ['placeholder' => 'Repeat password']))
			->add('email', EmailType::class, array('label' => 'E-mail address', 'attr' => ['placeholder' => 'E-mail']))
			->add('language', ChoiceType::class, array('label' => 'Language', 'choices' => $options['languageRepository']->getFormChoices()))
			->add('acceptRules', CheckboxType::class);
		
		$builder->get('language')->addModelTransformer(new EntityTransformer($options['languageRepository']));
	}

	public function getName()
	{
		return 'UserRegistration';
	}
}