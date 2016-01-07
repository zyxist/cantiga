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

use Cantiga\CoreBundle\Repository\AreaRepositoryInterface;
use Cantiga\Metamodel\Form\EntityTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use WIO\EdkBundle\Entity\EdkRoute;

class EdkRouteForm extends AbstractType
{
	const ADD = 0;
	const EDIT = 1;
	
	private $mode;
	private $areaRepository;
		
	public function __construct($mode, AreaRepositoryInterface $areaRepository = null)
	{
		$this->mode = $mode;
		$this->areaRepository = $areaRepository;
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{    
		$resolver->setDefaults(array(
			'translation_domain' => 'edk'
		));
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		if (!empty($this->areaRepository)) {
			$builder->add('area', new ChoiceType, ['label' => 'Area', 'choices' => $this->areaRepository->getFormChoices()]);
			$builder->get('area')->addModelTransformer(new EntityTransformer($this->areaRepository));
		}
		
		$builder
			->add('routeType', new ChoiceType, ['label' => 'Route type', 'choices' => [EdkRoute::TYPE_FULL => 'FullRoute', EdkRoute::TYPE_INSPIRED => 'RouteInspiredByEWC']])
			->add('name', new TextType, array('label' => 'Route name'))
			->add('routeCourse', new TextareaType, array('label' => 'Route course', 'attr' => array('help_text' => 'RouteCourseInfoText')))
			->add('routeFrom', new TextType, 
				array('label' => 'Route beginning', 'attr' => array('help_text' => '(settlement)'))
			)
			->add('routeTo', new TextType, 
				array('label' => 'Route end', 'attr' => array('help_text' => '(settlement)'))
			)
			->add('routeLength', new IntegerType, 
				array('label' => 'Route length (km)')
			)
			->add('routeAscent', new IntegerType, 
				array('label' => 'Route ascent (m)', 'attr' => array('help_text' => 'RouteAscentInfoText'))
			)
			->add('routeObstacles', new TextType, 
				array('label' => 'Additional obstacles', 'required' => false)
			)
			->add('descriptionFileUpload', new FileType, array('label' => 'RouteDescriptionFileUpload', 'required' => false))
			->add('mapFileUpload', new FileType, array('label' => 'RouteMapFileUpload', 'required' => false, 'attr' => array('help_text' => 'RouteMapCopyrightInformationText')))
			->add('gpsTrackFileUpload', new FileType, array('label' => 'RouteGPSTraceFileUpload', 'required' => $this->mode == self::ADD))
			->add('save', new SubmitType, array('label' => 'Save'));
	}
	
	public function getName() {
		return 'EdkRoute';
	}
}
