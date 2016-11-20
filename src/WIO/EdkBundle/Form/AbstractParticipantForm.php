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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use WIO\EdkBundle\Entity\EdkRegistrationSettings;
use WIO\EdkBundle\Entity\WhereLearntAbout;

/**
 * Base class for the all forms related to the participant registration.
 *
 * @author Tomasz Jędrzejewski
 */
abstract class AbstractParticipantForm extends AbstractType
{
	/**
	 * @var EdkRegistrationSettings
	 */
	protected $registrationSettings;

	public function __construct(EdkRegistrationSettings $rs = null)
	{
		$this->registrationSettings = $rs;
	}
	
	protected abstract function isMailRequired();

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
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
			->add('email', EmailType::class, ['label' => 'E-mail address', 'required' => $this->isMailRequired(), 'attr' => 
				$this->isMailRequired() ? [] : ['help_text' => 'EmailNotRequiredHelpText']
			])
			->add('age', IntegerType::class, ['label' => 'Age', 'attr' => ['help_text' => 'AgeHelpText']])
			->add('sex', ChoiceType::class, ['label' => 'Sex', 'choices' => [
					1 => 'male',
					2 => 'female'
				], 'multiple' => false, 'expanded' => true]
			)
			->add('howManyTimes', IntegerType::class, ['label' => 'HowManyTimesField'])
			->add('whereLearnt', ChoiceType::class, [
				'label' => 'WhereHaveYouLearntAboutField',
				'empty_value' => '-- choose --',
				'empty_data' => null,
				'choices' => WhereLearntAbout::getFormChoices()
			])
			->add('whereLearntOther', TextType::class, array('label' => 'WhereHaveYouLearntAboutContField', 'required' => false))
			->add('whyParticipate', TextareaType::class, array('label' => 'WhyParticipateField', 'required' => false))
			->add('save', SubmitType::class, array('label' => $this->getRegisterButtonText()));
		$builder->add('customAnswer', TextareaType::class, array(
			'label' => $this->getCustomQuestion(), 'required' => $this->isCustomQuestionRequired()
		));
	}

	public function isPeopleNumEditable()
	{
		if (null !== $this->registrationSettings) {
			return $this->registrationSettings->getMaxPeoplePerRecord() > 1;
		}
		return true;
	}

	public function getCustomQuestion()
	{
		if (null !== $this->registrationSettings) {
			$value = $this->registrationSettings->getCustomQuestion();
			if(!empty($value)) {
				return $this->registrationSettings->getCustomQuestion();
			}
		}
		return 'Additional information';
	}

	public function isCustomQuestionRequired()
	{
		if (null !== $this->registrationSettings) {
			$value = $this->registrationSettings->getCustomQuestion();
			return (!empty($value));
		}
		return false;
	}
	
	public function getRegisterButtonText()
	{
		return 'Register';
	}
}
