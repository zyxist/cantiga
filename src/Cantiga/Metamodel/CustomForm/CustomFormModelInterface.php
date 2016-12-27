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
declare(strict_types=1);
namespace Cantiga\Metamodel\CustomForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Allows form customization, e.g. for area profile or area requests.
 */
interface CustomFormModelInterface
{
	/**
	 * Configures the form fields.
	 * 
	 * @param FormBuilderInterface $builder
	 */
	public function constructForm(FormBuilderInterface $builder);
	/**
	 * Custom validation code for the form, in addition to the default validators.
	 * 
	 * @param array $data Form data
	 * @param ExecutionContextInterface $context
	 */
	public function validateForm(array $data, ExecutionContextInterface $context);
	/**
	 * Specifies how the custom form shall be rendered. 
	 */
	public function createFormRenderer(): CustomFormRendererInterface;
	/**
	 * Specifies how the form data are presented in the detail page.
	 */
	public function createSummary(): CustomFormSummaryInterface;
}
