<?php
namespace Cantiga\Metamodel\Form;

/**
 * @author Tomasz Jędrzejewski
 */
interface EntityTransformerInterface
{
	public function transformToKey($entity);
	public function transformToEntity($key);
}
