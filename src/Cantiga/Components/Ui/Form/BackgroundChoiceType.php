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

namespace Cantiga\Components\Ui\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A selector for backgrounds.
 */
class BackgroundChoiceType extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'choices' => array(
				'Blue' => 'primary',
				'Light blue' => 'info',
				'Green' => 'green',
				'Orange' => 'orange',
				'Red' => 'red',
				'Gray' => 'gray',
				'Navy' => 'navy',
				'Teal' => 'teal',
				'Purple' => 'purple',
				'Maroon' => 'maroon',
				'Black' => 'black'
			)
		));
	}

	public function getParent()
	{
		return ChoiceType::class;
	}

}
