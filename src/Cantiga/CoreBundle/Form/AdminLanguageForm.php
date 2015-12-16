<?php
namespace Cantiga\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AdminLanguageForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', 'text', array('label' => 'Name'))
			->add('locale', 'text', array('label' => 'Locale'))
			->add('save', 'submit', array('label' => 'Save'));
	}

	public function getName()
	{
		return 'Language';
	}
}
