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
namespace Cantiga\Metamodel\CustomForm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Callback;

/**
 * Helper for embedding the custom forms in the actual form.
 * 
 * @author Tomasz Jędrzejewski
 */
class CustomFormType extends AbstractType
{
	private $callback;
	private $validationCallback;
	
	public function __construct($callback, $validationCallback)
	{
		$this->callback = $callback;
		$this->validationCallback = $validationCallback;
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(
			array('constraints' => array(new Callback(array('methods' => array($this->validationCallback)))))
		);
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$callback = $this->callback;
		$callback($builder);
	}

	public function getName()
	{
		return 'CustomForm';
	}
}
