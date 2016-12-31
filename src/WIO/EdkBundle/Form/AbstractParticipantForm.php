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
namespace WIO\EdkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WIO\EdkBundle\Entity\WhereLearntAbout;

/**
 * Base class for the all forms related to the participant registration.
 */
abstract class AbstractParticipantForm extends AbstractType
{
	protected abstract function isMailRequired(array $options);

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefined(['registrationSettings']);
		$resolver->setDefaults([
			'translation_domain' => 'public',
			'csrf_protection' => false,
		]);
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('firstName', TextType::class, ['label' => 'First name'])
			->add('lastName', TextType::class, ['label' => 'Last name'])
			->add('email', EmailType::class, ['label' => 'E-mail address', 'required' => $this->isMailRequired($options), 'attr' => 
				$this->isMailRequired($options) ? [] : ['help_text' => 'EmailNotRequiredHelpText']
			])
			->add('age', IntegerType::class, ['label' => 'Age', 'attr' => ['help_text' => 'AgeHelpText']])
			->add('sex', ChoiceType::class, ['label' => 'Sex', 'choices' => [
					'male' => 1,
					'female' => 2
				], 'multiple' => false, 'expanded' => true]
			)
			->add('howManyTimes', IntegerType::class, ['label' => 'HowManyTimesField'])
			->add('whereLearnt', ChoiceType::class, [
				'label' => 'WhereHaveYouLearntAboutField',
				'choices' => WhereLearntAbout::getFormChoices()
			])
			->add('whereLearntOther', TextType::class, array('label' => 'WhereHaveYouLearntAboutContField', 'required' => false))
			->add('whyParticipate', TextareaType::class, array('label' => 'WhyParticipateField', 'required' => false))
			->add('save', SubmitType::class, array('label' => $this->getRegisterButtonText($options)));
		$builder->add('customAnswer', TextareaType::class, array(
			'label' => $this->getCustomQuestion($options), 'required' => $this->isCustomQuestionRequired($options)
		));
	}

	public function isPeopleNumEditable(array $options)
	{
		if (!empty($options['registrationSettings'])) {
			return $options['registrationSettings']->getMaxPeoplePerRecord() > 1;
		}
		return true;
	}

	public function getCustomQuestion(array $options)
	{
		if (!empty($options['registrationSettings'])) {
			$value = $options['registrationSettings']->getCustomQuestion();
			if(!empty($value)) {
				return $options['registrationSettings']->getCustomQuestion();
			}
		}
		return 'Additional information';
	}

	public function isCustomQuestionRequired(array $options)
	{
		if (!empty($options['registrationSettings'])) {
			$value =$options['registrationSettings']->getCustomQuestion();
			return (!empty($value));
		}
		return false;
	}
	
	public function getRegisterButtonText(array $options)
	{
		return 'Register';
	}
}
