<?php
namespace Cantiga\MilestoneBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MilestoneForm extends AbstractType
{
	const TYPE_AREA = 'Area';
	const TYPE_GROUP = 'Group';
	
	private $isNew;
	
	public function __construct($isNew)
	{
		$this->isNew = (bool) $isNew;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', new TextType, array('label' => 'Name'))
			->add('description', new TextType, array('label' => 'Description'))
			->add('displayOrder', new NumberType, array('label' => 'Display order'));
		if ($this->isNew) {
			$builder->add('entityType', new ChoiceType, array('label' => 'Where shown?', 'choices' => [self::TYPE_AREA => 'Area', self::TYPE_GROUP => 'Group']));
		}
		$builder
			->add('deadline', new DateType, array('label' => 'Deadline', 'input' => 'string', 'empty_value' => '-- none --', 'required' => false))
			->add('save', 'submit', array('label' => 'Save'));
	}

	public function getName()
	{
		return 'Milestone';
	}
}