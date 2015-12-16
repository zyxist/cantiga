<?php
namespace Cantiga\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Cantiga\CoreBundle\Entity\User;

class UserProfileForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('location', 'text', array('label' => 'Location', 'required' => false))
			->add('telephone', 'text', array('label' => 'Telephone', 'required' => false))
			->add('publicMail', 'text', array('label' => 'Public e-mail', 'required' => false))
			->add('notes', 'text', array('label' => 'Notes', 'required' => false))
			->add('privShowTelephone', 'choice', array('label' => 'Who can see my phone number?', 'choices' => User::getPrivacySettings()))
			->add('privShowPublicMail', 'choice', array('label' => 'Who can see my public e-mail?', 'choices' => User::getPrivacySettings()))
			->add('privShowNotes', 'choice', array('label' => 'Who can see my notes?', 'choices' => User::getPrivacySettings()))
			->add('save', 'submit', array('label' => 'Save'));
	}

	public function getName()
	{
		return 'UserProfile';
	}
}