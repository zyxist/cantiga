<?php
namespace Cantiga\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Cantiga\CoreBundle\Repository\LanguageRepository;

class PasswordRecoveryCompleteForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('password', 'password', array('label' => 'Password', 'attr' => ['placeholder' => 'New password']))
			->add('repeatPassword', 'password', array('label' => 'Repeat password', 'attr' => ['placeholder' => 'Repeat password']));
	}

	public function getName()
	{
		return 'PasswordRecoveryComplete';
	}
}