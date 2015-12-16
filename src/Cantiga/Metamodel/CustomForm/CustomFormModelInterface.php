<?php
namespace Cantiga\Metamodel\CustomForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;



/**
 * @author Tomasz Jędrzejewski
 */
interface CustomFormModelInterface
{
	public function constructForm(FormBuilderInterface $builder);
	public function validateForm($data, ExecutionContextInterface $context);
	public function createFormRenderer();
}
