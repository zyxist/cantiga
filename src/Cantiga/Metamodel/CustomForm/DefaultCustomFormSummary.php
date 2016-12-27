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

use ArrayIterator;
use IteratorAggregate;

/**
 * Presents the data from the custom form on a summary page.
 */
class DefaultCustomFormSummary implements CustomFormSummaryInterface, IteratorAggregate
{
	const TYPE_STRING = 'string';
	const TYPE_BOOLEAN = 'boolean';
	const TYPE_DATE = 'date';
	const TYPE_CALLBACK = 'callback';
	const TYPE_CALLBACK_RAW = 'callback-raw';
	const TYPE_CHOICE = 'choice';
	const TYPE_URL = 'url';
	
	private $properties = array();
	
	public function getTemplate(): string
	{
		return 'CantigaCoreBundle:layout:custom-summary.html.twig';
	}
	
	/**
	 * Specifies the form property to display. The method configures the label, and the way the value shall be
	 * presented. For very specific rendering, it is possible to use a callback function.
	 * 
	 * @param string $property
	 * @param string $label
	 * @param string $type Data type (use the class constants)
	 * @param callable $callback Custom rendering callback (set the type as TYPE_CALLBACK or TYPE_CALLBACK_RAW).
	 */
	public function present(string $property, string $label, string $type, $callback = null)
	{
		$this->properties[] = ['name' => $property, 'label' => $label, 'type' => $type, 'callback' => $callback];
	}
	
	public function getIterator()
	{
		return new ArrayIterator($this->properties);
	}
}
