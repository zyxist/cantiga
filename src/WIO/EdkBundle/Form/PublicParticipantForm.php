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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Public version of the registration form. Requires specifying an additional text about
 * accepting terms, which is customizable.
 */
class PublicParticipantForm extends AbstractParticipantForm
{
	public function configureOptions(OptionsResolver $resolver)
	{
		parent::configureOptions($resolver);
		$resolver->setDefined(['texts']);
		$resolver->setRequired(['texts']);
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		parent::buildForm($builder, $options);
		$builder->add('terms1Accepted', BooleanType::class, ['label' => $options['texts'][1], 'required' => true]);
		$builder->add('terms2Accepted', BooleanType::class, ['label' => $options['texts'][2], 'required' => true]);
		$builder->add('terms3Accepted', BooleanType::class, ['label' => $options['texts'][3], 'required' => true]);
	}
	
	protected function isMailRequired(array $options)
	{
		return true;
	}

	public function getName()
	{
		return 'Participant';
	}
}
