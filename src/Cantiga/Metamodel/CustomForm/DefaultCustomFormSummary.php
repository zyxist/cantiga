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

use ArrayIterator;
use IteratorAggregate;

/**
 * Presents the data from the custom form on a summary page.
 *
 * @author Tomasz Jędrzejewski
 */
class DefaultCustomFormSummary implements CustomFormSummaryInterface, IteratorAggregate
{
	private $properties = array();
	
	public function getTemplate()
	{
		return 'CantigaCoreBundle:layout:custom-summary.html.twig';
	}
	
	public function present($property, $label, $type, $callback = null)
	{
		$this->properties[] = ['name' => $property, 'label' => $label, 'type' => $type, 'callback' => $callback];
	}
	
	public function getIterator()
	{
		return new ArrayIterator($this->properties);
	}
}
