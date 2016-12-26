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
namespace Cantiga\CourseBundle\Form;

use Cantiga\CoreBundle\Form\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Tomasz Jędrzejewski
 */
class CourseForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', TextType::class, array('label' => 'Name'))
			->add('description', TextType::class, array('label' => 'Course description', 'attr' => array('help_text' => 'VisibleToParticipantsHint')))
			->add('authorName', TextType::class, array('label' => 'CourseAuthorLabel'))
			->add('authorEmail', EmailType::class, array('label' => 'CourseAuthorEmailLabel'))
			->add('presentationLink', UrlType::class, array('label' => 'Presentation URL', 'attr' => array('help_text' => 'Google Slides, Prezi')))
			->add('deadline', DateType::class, array('label' => 'Deadline', 'input' => 'timestamp', 'required' => false))
			->add('displayOrder', NumberType::class, array('label' => 'Display order'))
			->add('notes', TextareaType::class, array('label' => 'Notes', 'required' => false, 'attr' => array('help_text' => 'NotVisibleToParticipantsHint', 'rows' => 10)))
			->add('isPublished', BooleanType::class, array('label' => 'Is published?', 'required' => false))
			->add('save', SubmitType::class, array('label' => 'Save'));
	}
	
	public function getName()
	{
		return 'Course';
	}
}
