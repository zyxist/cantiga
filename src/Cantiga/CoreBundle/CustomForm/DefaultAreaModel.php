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
namespace Cantiga\CoreBundle\CustomForm;

use Cantiga\Metamodel\CustomForm\CustomFormModelInterface;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormRenderer;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormSummary;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Tomasz Jędrzejewski
 */
class DefaultAreaModel implements CustomFormModelInterface
{
	public function constructForm(FormBuilderInterface $builder)
	{
		$builder->add('description', new TextType, array('label' => 'Description', 'constraints' => [
			new NotNull,
			new Length(['min' => 2, 'max' => 250])
		]));
	}
	
	public function validateForm($data, ExecutionContextInterface $context)
	{
	}
	
	public function createFormRenderer()
	{
		$r = new DefaultCustomFormRenderer();
		$r->group('Area information');
		$r->fields('description');
		return $r;
	}
	
	public function createSummary()
	{
		$s = new DefaultCustomFormSummary();
		$s->present('description', 'Description', 'string');
		return $s;
	}
}
