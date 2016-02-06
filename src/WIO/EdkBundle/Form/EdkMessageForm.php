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
namespace WIO\EdkBundle\Form;

use Cantiga\Metamodel\Form\EntityTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use WIO\EdkBundle\Repository\EdkPublishedDataRepository;


/**
 * @author Tomasz Jędrzejewski
 */
class EdkMessageForm extends AbstractType
{
	private $repository;
	
	public function __construct(EdkPublishedDataRepository $repository)
	{
		$this->repository = $repository;
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{    
		$resolver->setDefaults(array(
			'translation_domain' => 'public'
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('area', new ChoiceType, [
				'label' => 'Choose an area',
				'choices' => $this->repository->getFormChoices()
			])
			->add('subject', new TextType, ['label' => 'Subject'])
			->add('content', new TextareaType, array('label' => 'Content', 'attr' => ['rows' => 20]))
			->add('authorName', new TextType, array('label' => 'What is your name?'))
			->add('authorEmail', new TextType, array('label' => 'Your e-mail', 'required' => false))
			->add('authorPhone', new TextType, array('label' => 'Your phone number', 'required' => false))
			->add('save', 'submit', array('label' => 'Send message'));
		$builder->get('area')->addModelTransformer(new EntityTransformer($this->repository));
	}

	public function getName()
	{
		return 'EdkMessage';
	}
}
