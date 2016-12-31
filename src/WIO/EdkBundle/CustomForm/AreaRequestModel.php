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
declare(strict_types=1);
namespace WIO\EdkBundle\CustomForm;

use Cantiga\Metamodel\CustomForm\CustomFormModelInterface;
use Cantiga\Metamodel\CustomForm\CustomFormRendererInterface;
use Cantiga\Metamodel\CustomForm\CustomFormSummaryInterface;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormRenderer;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormSummary;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AreaRequestModel implements CustomFormModelInterface
{
	private $translator;
	
	public function __construct(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}
	
	public function constructForm(FormBuilderInterface $builder)
	{
		$builder->add('ewcType', ChoiceType::class, array('label' => 'Type of the way you wish to create', 'choices' => array_flip($this->ewcTypes())));
		
		$builder->add('routeFrom', TextType::class, array('label' => 'Beginning of the route', 'constraints' => [
			new NotNull,
			new Length(['min' => 2, 'max' => 50])
		]));
		$builder->add('routeTo', TextType::class, array('label' => 'End of the route', 'constraints' => [
			new NotNull,
			new Length(['min' => 2, 'max' => 50])
		]));
		$builder->add('routeLength', NumberType::class, array('label' => 'Route length', 'attr' => ['help_text' => 'In kilometres'], 'constraints' => [
			new Range(['min' => 20, 'max' => 100])
		]));
		$builder->add('routeAscent', NumberType::class, array('label' => 'Route ascent', 'attr' => ['help_text' => 'In metres'], 'constraints' => [
			new Range(['min' => 0, 'max' => 5000])
		]));
		
		$builder->add('whyCreatingArea', TextareaType::class, array('label' => 'WhyCreatingAreaFormLabel', 'attr' => ['help_text' => 'Max400Chars'], 'constraints' => [
			new NotNull,
			new Length(['min' => 10, 'max' => 400])
		]));
		$builder->add('intersectionPoint', TextareaType::class, array('label' => 'IntersectionPointFormLabel', 'attr' => ['help_text' => 'Max400Chars'], 'constraints' => [
			new NotNull,
			new Length(['min' => 10, 'max' => 400])
		]));
		$builder->add('leaderGoals', TextareaType::class, array('label' => 'LeaderGoalsFormLabel', 'attr' => ['help_text' => 'Max400Chars'], 'constraints' => [
			new NotNull,
			new Length(['min' => 10, 'max' => 400])
		]));
		$builder->add('particiaptionDetails', TextareaType::class, array('label' => 'ParticipationDetailsFormLabel', 'attr' => ['help_text' => 'Max400Chars'], 'constraints' => [
			new NotNull,
			new Length(['min' => 3, 'max' => 400])
		]));
		$builder->add('projectMgmtExperiences', TextareaType::class, array('label' => 'ProjectMgmtExperienceFormLabel', 'attr' => ['help_text' => 'ProjectMgmtExperiencesHelp'], 'constraints' => [
			new NotNull,
			new Length(['min' => 3, 'max' => 400])
		]));
		$builder->add('threeBiggestSuccesses', TextareaType::class, array('label' => 'ThreeBiggestSuccessesFormLabel', 'attr' => ['help_text' => 'Max400Chars'], 'constraints' => [
			new NotNull,
			new Length(['min' => 10, 'max' => 400])
		]));
		$builder->add('stationaryCourse', ChoiceType::class, ['label' => 'StationaryCoursePreferenceLabel', 'choices' => array_flip($this->stationaryCourseTypes()), 'multiple' => true, 'expanded' => true, 'constraints' => new Count(
				['min' => 1, 'minMessage' => 'Please select at least one option']
			)]);
	}
	
	public function validateForm(array $data, ExecutionContextInterface $context)
	{
		if ($data['ewcType'] == 'full') {
			if ($data['routeLength'] < 30) {
				$violation = $context->buildViolation('For full areas, the route length cannot be lower than 30 kilometers.')
					->atPath('[routeLength]')
					->addViolation();
				return false;
			} else if($data['routeLength'] >= 30 && $data['routeLength'] < 40 && $data['routeAscent'] < 500) {
				$context->buildViolation('The route ascent must be greater than 500 meters, if the length is between 30 and 40 kilometers.')
					->atPath('[routeAscent]')
					->addViolation();
				return false;
			}
		}
		return true;
	}
	
	public function createFormRenderer(): CustomFormRendererInterface
	{
		$r = new DefaultCustomFormRenderer();
		$r->group('Your Extreme Way');
		$r->fields('ewcType');
		$r->group('Proposed route', 'ProposedRouteInformationText');
		$r->fields('routeFrom', 'routeTo');
		$r->fields('routeLength', 'routeAscent');
		$r->group('About you', 'AboutYouEwcText');
		$r->fields('whyCreatingArea', 'intersectionPoint', 'leaderGoals', 'particiaptionDetails', 'projectMgmtExperiences', 'threeBiggestSuccesses');
		$r->group('Stationary course');
		$r->fields('stationaryCourse');
		return $r;
	}
	
	public function createSummary(): CustomFormSummaryInterface
	{
		$s = new DefaultCustomFormSummary();
		$s->present('ewcType', 'Type of the way', 'choice', $this->ewcTypes());
		$s->present('routeFrom', 'Beginning of the route', 'string');
		$s->present('routeTo', 'End of the route', 'string');
		$s->present('routeLength', 'Route length', 'callback', function($length) {
			return $length.' km';
		});
		$s->present('routeAscent', 'Route ascent', 'callback', function($length) {
			return $length.' m';
		});
		$s->present('whyCreatingArea', 'WhyCreatingAreaFormLabel', 'string');
		$s->present('intersectionPoint', 'IntersectionPointFormLabel', 'string');
		$s->present('leaderGoals', 'LeaderGoalsFormLabel', 'string');
		$s->present('particiaptionDetails', 'ParticipationDetailsFormLabel', 'string');
		$s->present('projectMgmtExperiences', 'ProjectMgmtExperienceFormLabel', 'string');
		$s->present('threeBiggestSuccesses', 'ThreeBiggestSuccessesFormLabel', 'string');
		$s->present('stationaryCourse', 'StationaryCoursePreferenceLabel', 'callback', function($options) {
			if (!is_array($options)) {
				return '---';
			}
			$code = '<ul>';
			$mapping = $this->stationaryCourseTypes();
			foreach ($options as $option) {
				$code .= '<li>'.$this->translator->trans($mapping[$option]).'</li>';
			}
			$code .= '</ul>';
			return $code;			
		});
		return $s;
	}
	
	public function ewcTypes()
	{
		return ['full' => 'Extreme Way of the Cross', 'inspired' => 'Inspired by Extreme Way of the Cross'];
	}
	
	public function stationaryCourseTypes()
	{
		return [
			1 => '2016-01-23',
			2 => '2016-01-30',
			3 => '2016-01-31',
			4 => '2016-02-06',
			5 => 'None of the above'
		];
	}
}
