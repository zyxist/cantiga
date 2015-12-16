<?php
namespace Cantiga\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Cantiga\CoreBundle\Api\AppMails;

class AdminAppMailForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('place', 'choice', array('label' => 'Place', 'choices' => AppMails::getNames(), 'attr' => array('help_text' => 'Place where the message is sent.')))
			->add('subject', 'text', array('label' => 'Subject'))
			->add('content', 'textarea', array('label' => 'Content', 'attr' => ['rows' => 20]))
			->add('locale', 'text', array('label' => 'Locale', 'attr' => array('help_text' => 'Must match one of the installed languages.')))
			->add('save', 'submit', array('label' => 'Save'));
	}

	public function getName()
	{
		return 'AppMail';
	}
}