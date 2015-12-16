<?php
namespace Cantiga\Metamodel\CustomForm;

/**
 * Description of FieldGroup
 *
 * @author Tomasz Jędrzejewski
 */
class FieldGroup implements \IteratorAggregate
{
	private $name;
	private $description;
	private $fields = array();
	
	public function __construct($name, $description)
	{
		$this->name = $name;
		$this->description = $description;
	}
	
	public function addField($name)
	{
		$this->fields[] = $name;
	}
	
	public function getName()
	{
		return $this->name;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getFields()
	{
		return $this->fields;
	}
	
	public function getIterator()
	{
		return new \ArrayIterator($this->fields);
	}
}
