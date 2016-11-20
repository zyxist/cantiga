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

use Cantiga\CoreBundle\Api\AppMails;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AdminAppMailForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('place', ChoiceType::class, array('label' => 'Place', 'choices' => AppMails::getNames(), 'attr' => array('help_text' => 'Place where the message is sent.')))
			->add('subject', TextType::class, array('label' => 'Subject'))
			->add('content', TextareaType::class, array('label' => 'Content', 'attr' => ['rows' => 20]))
			->add('locale', TextType::class, array('label' => 'Locale', 'attr' => array('help_text' => 'Must match one of the installed languages.')))
			->add('save', SubmitType::class, array('label' => 'Save'));
	}

	public function getName()
	{
		return 'AppMail';
	}
}