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

use Cantiga\CoreBundle\Form\Type\BooleanType;
use DateTimeZone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use WIO\EdkBundle\Entity\EdkRegistrationSettings;

class EdkRegistrationSettingsForm extends AbstractType
{
	/**
	 * @var DateTimeZone
	 */
	private $timezone;

	public function __construct(DateTimeZone $timezone)
	{
		$this->timezone = $timezone;
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{    
		$resolver->setDefaults(array(
			'translation_domain' => 'edk'
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('registrationType', new ChoiceType, [
				'label' => 'Registration type',
				'choices' => EdkRegistrationSettings::getRegistrationTypes()
			])
			->add('startTime', new DateTimeType, array(
				'label' => 'Beginning of registration',
				'years' => $this->currentYears(),
				'input' => 'timestamp',
				'model_timezone' => 'UTC',
				'view_timezone' => $this->timezone->getName(),
				'required' => false,
			))
			->add('endTime', new DateTimeType, array(
				'label' => 'End of registration',
				'years' => $this->currentYears(),
				'input' => 'timestamp',
				'model_timezone' => 'UTC',
				'view_timezone' => $this->timezone->getName(),
				'required' => false
			))
			->add('externalRegistrationUrl', new UrlType, ['label' => 'External registration URL', 'attr' => ['help_text' => 'ExternalRegistrationUrlHint'], 'required' => false])
			->add('participantLimit', new NumberType, array('label' => 'Expected number of participants', 'required' => false))
			->add('allowLimitExceed', new BooleanType, array('label' => 'Allow exceeding the participant limit', 'attr' => array('help_text' => 'AllowLimitExceedHint'), 'required' => false))
			->add('maxPeoplePerRecord', new NumberType, array('label' => 'Max. number of people in the record', 'attr' => array('help_text' => 'MaxPeoplePerRecordHint'), 'required' => false))
			->add('customQuestion', new TextType, array('label' => 'Custom question', 'attr' => array('help_text' => 'CustomQuestionHint'), 'required' => false))
			->add('save', 'submit', array('label' => 'Save'));
	}

	public function getName()
	{
		return 'EdkRegistrationSettings';
	}

	private function currentYears()
	{
		$yr = date('Y');
		return array($yr => $yr);
	}

}
