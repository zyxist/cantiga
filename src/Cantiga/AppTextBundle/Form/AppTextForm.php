<?php
/*
 * This file is part of Cantiga Project. Copyright 2016-2017 Cantiga contributors.
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
 * along with Cantiga; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace Cantiga\AppTextBundle\Form;

use Cantiga\CoreBundle\Api\AppTexts;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppTextForm extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefault('translation_domain', 'apptext');
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('place', ChoiceType::class, ['label' => 'Place', 'choices' => AppTexts::getNames(), 'attr' => array('help_text' => 'AppTextPlaceHint')])
			->add('title', TextType::class, ['label' => 'Title', 'attr' => ['help_text' => 'AppTextTitleHint']])
			->add('content', TextareaType::class, ['label' => 'Content', 'attr' => ['rows' => 20]])
			->add('locale', TextType::class, ['label' => 'Locale', 'attr' => ['help_text' => 'AppTextLocaleHint']])
			->add('save', SubmitType::class, ['label' => 'Save', 'translation_domain' => 'general']);
	}

	public function getName()
	{
		return 'AppText';
	}
}
