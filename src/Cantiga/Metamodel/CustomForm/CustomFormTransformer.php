<?php
namespace Cantiga\Metamodel\CustomForm;

/**
 * Description of CustomFormTransformer
 *
 * @author Tomasz Jędrzejewski
 */
class CustomFormTransformer implements Symfony\Component\Form\DataTransformerInterface
{
	public function reverseTransform($value)
	{
		return json_encode($value);
	}

	public function transform($value)
	{
		return json_decode($value);
	}
}
