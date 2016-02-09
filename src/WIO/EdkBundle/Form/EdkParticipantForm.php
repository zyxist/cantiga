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

use Cantiga\Metamodel\Form\EntityTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
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
	private $routeRepository;
	
	public function __construct($mode, EdkRegistrationSettings $settings, $routeRepository = null)
	{
		parent::__construct($settings);
		$this->mode = (int) $mode;
		$this->routeRepository = $routeRepository;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		parent::buildForm($builder, $options);
		if($this->mode == self::ADD) {
			$builder->add('peopleNum', new IntegerType, ['label' => 'NumberRegisteredPeopleField']);
			$builder->add('route', new ChoiceType, [ 'label' => 'Route', 'choices' => $this->routeRepository->getRouteChoicesWithOpenRegistration()]);
			$builder->get('route')->addModelTransformer(new EntityTransformer($this->routeRepository));
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
}
