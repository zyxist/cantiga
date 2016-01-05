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
namespace Cantiga\MilestoneBundle\Form;

use Cantiga\MilestoneBundle\Entity\Milestone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MilestoneForm extends AbstractType
{
	const TYPE_AREA = 'Area';
	const TYPE_GROUP = 'Group';
	const TYPE_PROJECT = 'Project';
	
	private $isNew;
	
	public function __construct($isNew)
	{
		$this->isNew = (bool) $isNew;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', new TextType, array('label' => 'Name'))
			->add('description', new TextType, array('label' => 'Description'))
			->add('displayOrder', new NumberType, array('label' => 'Display order'));
		if ($this->isNew) {
			$builder->add('entityType', new ChoiceType, array('label' => 'Where shown?', 'choices' => [self::TYPE_AREA => 'Area', self::TYPE_GROUP => 'Group', self::TYPE_PROJECT => 'Project']));
			$builder->add('type', new ChoiceType, array('label' => 'How to count progress?', 'choices' => [Milestone::TYPE_BINARY => 'binary (yes-no)', Milestone::TYPE_PERCENT => '0-100%']));
		}
		$builder
			->add('deadline', new DateType, array('label' => 'Deadline', 'input' => 'timestamp', 'empty_value' => '-- none --', 'required' => false))
			->add('save', 'submit', array('label' => 'Save'));
	}

	public function getName()
	{
		return 'Milestone';
	}
}