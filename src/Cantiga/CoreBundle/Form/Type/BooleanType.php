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

namespace Cantiga\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Cantiga\Metamodel\Form\BooleanTransformer;

/**
 * Better rendering of boolean fields.
 *
 * @author Tomasz Jędrzejewski
 */
class BooleanType extends AbstractType
{

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'compound' => false,
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->addViewTransformer(new BooleanTransformer());
	}

	public function getBlockPrefix()
	{
		return 'boolean';
	}

	public function getName()
	{
		return 'boolean';
	}

}
