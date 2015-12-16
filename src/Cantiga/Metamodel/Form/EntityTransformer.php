<?php
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
