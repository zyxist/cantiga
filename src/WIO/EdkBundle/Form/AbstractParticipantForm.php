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
			->add('firstName', new TextType, ['label' => 'First name'])
			->add('lastName', new TextType, ['label' => 'Last name'])
			->add('email', new EmailType, ['label' => 'E-mail address', 'required' => $this->isMailRequired(), 'attr' => 
				$this->isMailRequired() ? [] : ['help_text' => 'EmailNotRequiredHelpText']
			])
			->add('age', new IntegerType, ['label' => 'Age', 'attr' => ['help_text' => 'AgeHelpText']])
			->add('sex', new ChoiceType, ['label' => 'Sex', 'choices' => [
					1 => 'male',
					2 => 'female'
				], 'multiple' => false, 'expanded' => true]
			)
			->add('howManyTimes', new IntegerType, ['label' => 'HowManyTimesField'])
			->add('whereLearnt', new ChoiceType, [
				'label' => 'WhereHaveYouLearntAboutField',
				'empty_value' => '-- choose --',
				'empty_data' => null,
				'choices' => WhereLearntAbout::getFormChoices()
			])
			->add('whereLearntOther', new TextType, array('label' => 'WhereHaveYouLearntAboutContField', 'required' => false))
			->add('whyParticipate', new TextareaType, array('label' => 'WhyParticipateField', 'required' => false))
			->add('save', new SubmitType, array('label' => 'Register'));
		$builder->add('customAnswer', new TextareaType, array(
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
}
