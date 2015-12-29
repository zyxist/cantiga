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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Cantiga\CoreBundle\Repository\LanguageRepository;
use Cantiga\Metamodel\Form\EntityTransformer;

class UserRegistrationForm extends AbstractType
{
	/**
	 * @var LanguageRepository
	 */
	private $languageRepository;
	
	public function __construct(LanguageRepository $repository)
	{
		$this->languageRepository = $repository;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', 'text', array('label' => 'Full name', 'attr' => ['placeholder' => 'Full name']))
			->add('login', 'text', array('label' => 'Login', 'attr' => ['placeholder' => 'Login']))
			->add('password', 'password', array('label' => 'Password', 'attr' => ['placeholder' => 'Password']))
			->add('repeatPassword', 'password', array('label' => 'Repeat password', 'attr' => ['placeholder' => 'Repeat password']))
			->add('email', 'email', array('label' => 'E-mail address', 'attr' => ['placeholder' => 'E-mail']))
			->add('language', 'choice', array('label' => 'Language', 'choices' => $this->languageRepository->getFormChoices()))
			->add('acceptRules', 'checkbox');
		
		$builder->get('language')->addModelTransformer(new EntityTransformer($this->languageRepository));
	}

	public function getName()
	{
		return 'UserRegistration';
	}
}