<?php

namespace Cantiga\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Tomasz Jędrzejewski
 */
class PasswordRecoveryRequestForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('login', 'text', array('label' => 'Your login', 'attr' => ['placeholder' => 'Your login']))
			->add('email', 'email', array('label' => 'E-mail used for registration', 'attr' => ['placeholder' => 'E-mail used for registration']));
	}

	public function getName()
	{
		return 'PasswordRecoveryInitial';
	}

}
