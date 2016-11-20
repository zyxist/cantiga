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
namespace Cantiga\Metamodel\CustomForm;

use ArrayIterator;
use IteratorAggregate;
use LogicException;

/**
 * Keeps the rendering information for the custom form, so that we can generate a valid HTML
 * for it.
 *
 * @author Tomasz Jędrzejewski
 */
class DefaultCustomFormRenderer implements CustomFormRendererInterface, IteratorAggregate
{
	private $structure;
	private $lastGroup;
	
	public function group($groupName, $groupDescription = null)
	{
		$this->lastGroup = new FieldGroup($groupName, $groupDescription);
		$this->structure[] = $this->lastGroup;
	}
	
	public function fields() {
		$names = func_get_args();
		if (null == $this->lastGroup) {
			throw new LogicException('DefaultCustomFormRenderer::fields(): call group() method first!');
		}
		foreach ($names as $name) {
			$this->lastGroup->addField($name);
		}
	}
	
	public function getTemplate()
	{
		return 'CantigaCoreBundle:layout:custom-form.html.twig';
	}

	public function getIterator()
	{
		return new ArrayIterator($this->structure);
	}
}
