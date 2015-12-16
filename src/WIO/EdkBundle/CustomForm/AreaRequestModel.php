<?php
namespace WIO\EdkBundle\CustomForm;

use Cantiga\Metamodel\CustomForm\CustomFormModelInterface;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormRenderer;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormSummary;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Tomasz Jędrzejewski
 */
class AreaRequestModel implements CustomFormModelInterface
{
	private $translator;
	
	public function __construct(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}
	
	public function constructForm(FormBuilderInterface $builder)
	{
		$builder->add('ewcType', new ChoiceType, array('label' => 'Type of the way you wish to create', 'choices' => $this->ewcTypes()));
		
		$builder->add('routeFrom', new TextType, array('label' => 'Beginning of the route', 'constraints' => [
			new NotNull,
			new Length(['min' => 2, 'max' => 50])
		]));
		$builder->add('routeTo', new TextType, array('label' => 'End of the route', 'constraints' => [
			new NotNull,
			new Length(['min' => 2, 'max' => 50])
		]));
		$builder->add('routeLength', new NumberType, array('label' => 'Route length', 'attr' => ['help_text' => 'In kilometres'], 'constraints' => [
			new Range(['min' => 20, 'max' => 100])
		]));
		$builder->add('routeAscent', new NumberType, array('label' => 'Route ascent', 'attr' => ['help_text' => 'In metres'], 'constraints' => [
			new Range(['min' => 0, 'max' => 5000])
		]));
	}
	
	public function validateForm($data, ExecutionContextInterface $context)
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
	
	public function createFormRenderer()
	{
		$r = new DefaultCustomFormRenderer();
		$r->group('Your Extreme Way');
		$r->fields('ewcType');
		$r->group('Proposed route', 'ProposedRouteInformationText');
		$r->fields('routeFrom', 'routeTo');
		$r->fields('routeLength', 'routeAscent');
		return $r;
	}
	
	public function createSummary()
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
		return $s;
	}
	
	public function ewcTypes()
	{
		return ['full' => 'Extreme Way of the Cross', 'inspired' => 'Inspired by Extreme Way of the Cross'];
	}
}
