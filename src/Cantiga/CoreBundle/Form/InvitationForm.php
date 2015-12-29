<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
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
