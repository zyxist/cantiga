<?php
namespace Cantiga\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

class InvitationForm extends AbstractType
{
	private $roles = array();
	
	public function __construct(array $roles)
	{
		$this->roles = $roles;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('email', 'email', array('label' => 'E-mail', 'attr' => array('help_text' => 'The invited person does not have to have an account.')))
			->add('role', 'choice', array('label' => 'Role', 'choices' => $this->asChoices($this->roles)))
			->add('note', 'text', array('label' => 'Note', 
				'constraints' => array(new Length(['min' => 0, 'max' => 30])),
				'attr' => array('help_text' => 'NoteHintText'))
			)
			->add('save', 'submit', array('label' => 'Submit invitation'));
	}
	
	private function asChoices(array $roles)
	{
		$results = array();
		foreach ($roles as $role) {
			$results[$role->getId()] = $role->getName();
		}
		return $results;
	}

	public function getName()
	{
		return 'Invitation';
	}
}
