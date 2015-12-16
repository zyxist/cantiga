<?php
namespace Cantiga\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserChangeEmailForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('password', 'password', array('label' => 'Password', 'attr' => ['placeholder' => 'Your current password']))
			->add('email', 'email', array('label' => 'E-mail', 'attr' => ['placeholder' => 'New e-mail address']))
			->add('save', 'submit', array('label' => 'Save'));

	}

	public function getName()
	{
		return 'UserChangeEmail';
	}
}