<?php
namespace Cantiga\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserChangePasswordForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('oldPassword', 'password', array('label' => 'Current password', 'attr' => ['placeholder' => 'Your current password']))
			->add('password', 'password', array('label' => 'New password', 'attr' => ['placeholder' => 'New password']))
			->add('repeatPassword', 'password', array('label' => 'Repeat password', 'attr' => ['placeholder' => 'Repeat password']))
			->add('save', 'submit', array('label' => 'Save'));

	}

	public function getName()
	{
		return 'UserChangeEmail';
	}
}