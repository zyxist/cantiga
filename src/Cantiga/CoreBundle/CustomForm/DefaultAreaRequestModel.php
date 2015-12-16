<?php
namespace Cantiga\CoreBundle\CustomForm;

use Cantiga\Metamodel\CustomForm\CustomFormModelInterface;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormRenderer;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormSummary;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Tomasz Jędrzejewski
 */
class DefaultAreaRequestModel implements CustomFormModelInterface
{
	public function constructForm(FormBuilderInterface $builder)
	{
		$builder->add('description', new TextType, array('label' => 'Description', 'constraints' => [
			new NotNull,
			new Length(['min' => 2, 'max' => 250])
		]));
	}
	
	public function validateForm($data, ExecutionContextInterface $context)
	{
	}
	
	public function createFormRenderer()
	{
		$r = new DefaultCustomFormRenderer();
		$r->group('Area information');
		$r->fields('description');
		return $r;
	}
	
	public function createSummary()
	{
		$s = new DefaultCustomFormSummary();
		$s->present('description', 'Description', 'string');
		return $s;
	}
}
