<?php
namespace Cantiga\CoreBundle\Form;

use Cantiga\CoreBundle\Form\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AdminUserForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', new TextType, array('label' => 'Name'))
			->add('email', new EmailType, array('label' => 'E-mail'))
			->add('active', new BooleanType, array('label' => 'Active?'))
			->add('admin', new BooleanType, array('label' => 'Is admin?'))
			->add('save', new SubmitType, array('label' => 'Save'));
	}

	public function getName()
	{
		return 'User';
	}
}