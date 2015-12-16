<?php
namespace Cantiga\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProjectAreaStatusForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', 'text', array('label' => 'Name'))
			->add('label', 'text', array('label' => 'CSS Label'))
			->add('isDefault', new Type\BooleanType(), array('label' => 'Is default?'))
			->add('save', 'submit', array('label' => 'Save'));
	}

	public function getName()
	{
		return 'AreaStatus';
	}
}