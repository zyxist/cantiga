<?php
namespace Cantiga\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Cantiga\Metamodel\Form\BooleanTransformer;

/**
 * Better rendering of boolean fields.
 *
 * @author Tomasz Jędrzejewski
 */
class BooleanType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'compound' => false,
        ));
    }
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->addViewTransformer(new BooleanTransformer());
	}
	
	public function getBlockPrefix()
	{
		return 'boolean';
	}

    public function getName()
    {
        return 'boolean';
    }
}
