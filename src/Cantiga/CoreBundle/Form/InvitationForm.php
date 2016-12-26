<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga contributors.
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

use Cantiga\CoreBundle\Form\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class InvitationForm extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefined(['roles', 'showContactsManageable', 'showContactHelpText']);
		$resolver->setRequired(['roles']);
		$resolver->setDefault('showContactsManageable', false);
		$resolver->setDefault('showContactHelpText', '');
		$resolver->setDefault('translation_domain', 'users');
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('email', EmailType::class, ['label' => 'E-mail', 'attr' => ['help_text' => 'The invited person does not have to have an account.']])
			->add('role', ChoiceType::class, ['label' => 'Role',
				'choices' => $this->asChoices($options['roles']),
				'attr' => ['help_text' => 'RoleHintText']])
			->add('note', TextType::class, ['label' => 'Function', 
				'constraints' => [new Length(['min' => 0, 'max' => 30])],
				'attr' => ['help_text' => 'NoteHintText']]
			)
			->add('save', SubmitType::class, array('label' => 'Submit invitation'));
		if ($options['showContactsManageable']) {
			$builder->add('showDownstreamContactData', BooleanType::class, ['label' => 'Show contacts to members of other places?', 'attr' => ['help_text' => $options['showContactHelpText']]]);
		}
	}
	
	private function asChoices(array $roles)
	{
		$results = array();
		foreach ($roles as $role) {
			$results[$role->getName()] = $role->getId();
		}
		return $results;
	}

	public function getName()
	{
		return 'Invitation';
	}
}
