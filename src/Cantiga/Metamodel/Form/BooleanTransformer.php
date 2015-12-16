<?php
namespace Cantiga\Metamodel\Form;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Description of BooleanTransformer
 *
 * @author Tomasz Jędrzejewski
 */
class BooleanTransformer implements DataTransformerInterface
{
	public function reverseTransform($value)
	{
		if($value == 'true' || $value == true || $value == 1 || $value == '1') {
			return 1;
		}
		return 0;
	}

	public function transform($value)
	{
		return ((int) $value) > 0 ? 1 : 0;
	}
}
