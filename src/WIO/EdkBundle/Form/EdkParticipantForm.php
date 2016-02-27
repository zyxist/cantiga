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
use Cantiga\Metamodel\Form\EntityTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use WIO\EdkBundle\Entity\EdkRegistrationSettings;

/**
 * Area version of the participant form. Has slightly different options.
 *
 * @author Tomasz Jędrzejewski
 */
class EdkParticipantForm extends AbstractParticipantForm
{
	const ADD = 0;
	const EDIT = 1;
	
	private $mode;
	private $settingsRepository;
	private $texts = [1 => '', 2 => '', 3 => ''];
	
	public function __construct($mode, EdkRegistrationSettings $settings = null, $settingsRepository = null)
	{
		parent::__construct($settings);
		$this->mode = (int) $mode;
		$this->settingsRepository = $settingsRepository;
	}
	
	public function setText($id, $text)
	{
		$this->texts[$id] = $text;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		parent::buildForm($builder, $options);
		if($this->mode == self::ADD) {
			$builder->add('peopleNum', new IntegerType, ['label' => 'NumberRegisteredPeopleField', 'attr' => ['help_text' => 'NumberRegisteredPeopleHintText']]);
			$builder->add('terms1Accepted', new BooleanType, ['label' => $this->texts[1]->getContent(), 'required' => true, 'disabled' => false]);
			$builder->add('terms2Accepted', new BooleanType, ['label' => $this->texts[2]->getContent(), 'required' => true, 'disabled' => false]);
			$builder->add('terms3Accepted', new BooleanType, ['label' => $this->texts[3]->getContent(), 'required' => true, 'disabled' => false]);
		}
	}
	
	protected function isMailRequired()
	{
		return false;
	}

	public function getName()
	{
		return 'Participant';
	}
	
	public function getRegisterButtonText()
	{
		return ($this->mode == self::ADD ? 'Add participant' : 'Change data');
	}
}
