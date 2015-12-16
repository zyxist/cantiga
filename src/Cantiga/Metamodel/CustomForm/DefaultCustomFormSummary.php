<?php
namespace Cantiga\Metamodel\CustomForm;

use ArrayIterator;
use IteratorAggregate;

/**
 * Description of DefaultCustomFormSummary
 *
 * @author Tomasz Jędrzejewski
 */
class DefaultCustomFormSummary implements CustomFormSummaryInterface, IteratorAggregate
{
	private $properties = array();
	
	public function getTemplate()
	{
		return 'CantigaCoreBundle:layout:custom-summary.html.twig';
	}
	
	public function present($property, $label, $type, $callback = null)
	{
		$this->properties[] = ['name' => $property, 'label' => $label, 'type' => $type, 'callback' => $callback];
	}
	
	public function getIterator()
	{
		return new ArrayIterator($this->properties);
	}
}
