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

use Cantiga\MilestoneBundle\Entity\Milestone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MilestoneForm extends AbstractType
{
	const TYPE_AREA = 'Area';
	const TYPE_GROUP = 'Group';
	const TYPE_PROJECT = 'Project';

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefined(['isNew']);
		$resolver->setRequired(['isNew']);
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', TextType::class, ['label' => 'Name', 'translation_domain' => 'general'])
			->add('description', TextType::class, array('label' => 'Description'))
			->add('displayOrder', NumberType::class, array('label' => 'Display order'));
		if ($options['isNew']) {
			$builder->add('entityType', ChoiceType::class, array('label' => 'Where shown?', 'choices' => array_flip([self::TYPE_AREA => 'Area', self::TYPE_GROUP => 'Group', self::TYPE_PROJECT => 'Project'])));
			$builder->add('type', ChoiceType::class, array('label' => 'How to count progress?', 'choices' => array_flip([Milestone::TYPE_BINARY => 'binary (yes-no)', Milestone::TYPE_PERCENT => '0-100%'])));
		}
		$builder
			->add('deadline', DateType::class, array('label' => 'Deadline', 'input' => 'timestamp', 'required' => false))
			->add('save', SubmitType::class, ['label' => 'Save', 'translation_domain' => 'general']);
	}

	public function getName()
	{
		return 'Milestone';
	}
}