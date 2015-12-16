<?php
namespace Cantiga\Metamodel\CustomForm;

use ArrayIterator;
use IteratorAggregate;
use LogicException;

/**
 * Description of DefaultCustomFormRenderer
 *
 * @author Tomasz Jędrzejewski
 */
class DefaultCustomFormRenderer implements CustomFormRendererInterface, IteratorAggregate
{
	private $structure;
	private $lastGroup;
	
	public function group($groupName, $groupDescription = null)
	{
		$this->lastGroup = new FieldGroup($groupName, $groupDescription);
		$this->structure[] = $this->lastGroup;
	}
	
	public function fields() {
		$names = func_get_args();
		if (null == $this->lastGroup) {
			throw new LogicException('DefaultCustomFormRenderer::fields(): call group() method first!');
		}
		foreach ($names as $name) {
			$this->lastGroup->addField($name);
		}
	}
	
	public function getTemplate()
	{
		return 'CantigaCoreBundle:layout:custom-form.html.twig';
	}

	public function getIterator()
	{
		return new ArrayIterator($this->structure);
	}
}
