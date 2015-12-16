<?php
namespace Cantiga\Metamodel\CustomForm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Callback;

/**
 * Description of CustomFormType
 *
 * @author Tomasz Jędrzejewski
 */
class CustomFormType extends AbstractType
{
	private $callback;
	private $validationCallback;
	
	public function __construct($callback, $validationCallback)
	{
		$this->callback = $callback;
		$this->validationCallback = $validationCallback;
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(
			array('constraints' => array(new Callback(array('methods' => array($this->validationCallback)))))
		);
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$callback = $this->callback;
		$callback($builder);
	}

	public function getName()
	{
		return 'CustomForm';
	}
}
