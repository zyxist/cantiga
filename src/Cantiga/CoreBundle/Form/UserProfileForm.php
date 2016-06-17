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

use Cantiga\CoreBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class UserProfileForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('location', TextType::class, ['label' => 'Location', 'required' => false])
			->add('telephone', TextType::class, ['label' => 'Telephone', 'required' => false])
			->add('publicMail', TextType::class, ['label' => 'Public e-mail', 'required' => false])
			->add('notes', TextType::class, ['label' => 'Notes', 'required' => false])
			->add('privShowTelephone', ChoiceType::class, ['label' => 'Who can see my phone number?', 'choices' => User::getPrivacySettings()])
			->add('privShowPublicMail', ChoiceType::class, ['label' => 'Who can see my public e-mail?', 'choices' => User::getPrivacySettings()])
			->add('privShowNotes', ChoiceType::class, ['label' => 'Who can see my notes?', 'choices' => User::getPrivacySettings()])
			->add('save', SubmitType::class, ['label' => 'Save']);
	}

	public function getName()
	{
		return 'UserProfile';
	}
}