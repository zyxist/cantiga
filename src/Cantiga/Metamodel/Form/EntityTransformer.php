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
namespace Cantiga\Metamodel\Form;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Cantiga\Metamodel\Exception\ItemNotFoundException;

/**
 * Helps handling choice form elements that represent a database relationship.
 * The transformer converts an entity into a key, and key back to the entity,
 * using the specified repository.
 *
 * @author Tomasz Jędrzejewski
 */
class EntityTransformer implements DataTransformerInterface
{
	private $repository;
	
	public function __construct(EntityTransformerInterface $repository)
	{
		$this->repository = $repository;
	}
	
	public function transform($value)
	{
		if (null === $value) {
			return '';
		}
		return $this->repository->transformToKey($value);
	}
	
	public function reverseTransform($value)
	{
		if (!$value) {
			return; // optional, finish
		}
		
		try {
			$entity = $this->repository->transformToEntity($value);
			if (null === $entity) {
				throw new TransformationFailedException('No such entity!');
			}
			return $entity;
		} catch(ItemNotFoundException $exception) {
			// Gives the ability to reuse getItem() method in transformToEntity()
			// getItem()'s throw exceptions by default.
			throw new TransformationFailedException('No such entity!', 0, $exception);
		}
	}
}
