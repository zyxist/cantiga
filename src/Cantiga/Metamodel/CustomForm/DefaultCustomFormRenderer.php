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
use LogicException;

/**
 * Configurable custom form rendered which displays the form fields in the FIELDSET groups,
 * two fields in a row in the widest layout.
 */
class DefaultCustomFormRenderer implements CustomFormRendererInterface, IteratorAggregate
{
	private $structure;
	private $lastGroup;
	
	/**
	 * Starts a new fieldset group. The group shall have a name, and an optional description
	 * displayed above the fields. The labels can be translated, using the <tt>messages</tt> message
	 * group.
	 * 
	 * @param string $groupName Group label
	 * @param string $groupDescription Optional group description
	 */
	public function group(string $groupName, string $groupDescription = null)
	{
		$this->lastGroup = new FieldGroup($groupName, $groupDescription);
		$this->structure[] = $this->lastGroup;
	}
	
	/**
	 * Specifies the names of the fields that shall be displayed in the recently created group. The
	 * method accepts a variadic number of string arguments.
	 * 
	 * @throws LogicException
	 */
	public function fields() {
		$names = func_get_args();
		if (null == $this->lastGroup) {
			throw new LogicException('DefaultCustomFormRenderer::fields(): call group() method first!');
		}
		foreach ($names as $name) {
			$this->lastGroup->addField($name);
		}
	}
	
	public function getTemplate(): string
	{
		return 'CantigaCoreBundle:layout:custom-form.html.twig';
	}

	public function getIterator()
	{
		return new ArrayIterator($this->structure);
	}
}
