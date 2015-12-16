<?php
namespace Cantiga\CoreBundle\Settings;

use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointFilter;
use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointsInterface;
use Cantiga\CoreBundle\Api\ModuleAwareInterface;
use Cantiga\CoreBundle\Form\Type\BooleanType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Represents an individual project setting. Project settings are configurable directly
 * from the application.
 *
 * @author Tomasz Jędrzejewski
 */
class Setting implements ModuleAwareInterface
{
	const TYPE_STRING = 0;
	const TYPE_INTEGER = 1;
	const TYPE_BOOLEAN = 2;
	const TYPE_EXTENSION_POINT = 3;
	
	private $key;
	private $name;
	private $module;
	private $value;
	private $type;
	private $extensionPoint;
	
	public function __construct($key, $name, $module, $value, $type, $extensionPoint = null)
	{
		$this->key = $key;
		$this->name = $name;
		$this->module = $module;
		$this->type = $type;
		$this->extensionPoint = $extensionPoint;
		$this->setValue($value);
	}
	
	public function getKey()
	{
		return $this->key;
	}
	
	public function getName()
	{
		return $this->name;
	}

	public function getModule()
	{
		return $this->module;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function getType()
	{
		return $this->type;
	}
	
	public function getExtensionPoint()
	{
		return $this->extensionPoint;
	}

	public function setValue($value)
	{
		switch ($this->type) {
			case self::TYPE_STRING:
				$this->value = (string) $value;
				break;
			case self::TYPE_INTEGER:
				$this->value = (int) $value;
				break;
			case self::TYPE_BOOLEAN:
				$this->value = (boolean) $value;
				break;
			case self::TYPE_EXTENSION_POINT:
				$this->value = (string) $value;
		}
		return $this;
	}
	
	/**
	 * Returns the setting value optimized for database usage.
	 * 
	 * @return mixed
	 */
	public function getNormalizedValue() {
		if ($this->type == self::TYPE_BOOLEAN) {
			return (int) $this->value;
		}
		return $this->value;
	}
	
	public function createEditor(FormBuilderInterface $builder, ExtensionPointsInterface $extensionPoints, ExtensionPointFilter $filter)
	{
		switch ($this->type) {
			case self::TYPE_STRING:
				$builder->add($this->key, 'text', array('label' => $this->name));
				break;
			case self::TYPE_INTEGER:
				$builder->add($this->key, 'integer', array('label' => $this->name));
				break;
			case self::TYPE_BOOLEAN:
				$builder->add($this->key, new BooleanType(), array('label' => $this->name));
				break;
			case self::TYPE_EXTENSION_POINT:
				$builder->add($this->key, 'choice', array('label' => $this->name, 'choices' =>
					$extensionPoints->describeImplementations($this->extensionPoint, $filter)
				));
				break;
		}
	}
}
