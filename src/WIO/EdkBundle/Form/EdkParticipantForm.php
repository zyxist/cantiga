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

use Cantiga\CoreBundle\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Area version of the participant form. Has slightly different options.
 */
class EdkParticipantForm extends AbstractParticipantForm
{
	const ADD = 0;
	const EDIT = 1;
	
	public function configureOptions(OptionsResolver $resolver)
	{
		parent::configureOptions($resolver);
		$resolver->setDefined(['texts', 'mode', 'settingsRepository']);
		$resolver->setRequired(['mode', 'settingsRepository']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		parent::buildForm($builder, $options);
		if($options['mode'] == self::ADD) {
			$builder->add('peopleNum', IntegerType::class, ['label' => 'NumberRegisteredPeopleField', 'attr' => ['help_text' => 'NumberRegisteredPeopleHintText']]);
			$builder->add('terms1Accepted', BooleanType::class, ['label' => $options['texts'][1], 'required' => true, 'disabled' => false]);
			$builder->add('terms2Accepted', BooleanType::class, ['label' => $options['texts'][2], 'required' => true, 'disabled' => false]);
			$builder->add('terms3Accepted', BooleanType::class, ['label' => $options['texts'][3], 'required' => true, 'disabled' => false]);
		}
	}
	
	protected function isMailRequired(array $options)
	{
		return false;
	}

	public function getName()
	{
		return 'Participant';
	}
	
	public function getRegisterButtonText(array $options)
	{
		return ($options['mode'] == self::ADD ? 'Add participant' : 'Change data');
	}
}
