<?php
namespace Cantiga\LinksBundle\Form;

use Cantiga\LinksBundle\Entity\Link;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class LinkForm extends AbstractType
{
	const PROJECT_SPECIFIC = 0;
	const GENERAL = 1;
	
	private $type;
	
	public function __construct($type)
	{
		$this->type = $type;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', new TextType, array('label' => 'Name'))
			->add('url', new UrlType, array('label' => 'Url'))
			->add('presentedTo', new ChoiceType, array('label' => 'Presentation place', 'choices' => $this->createChoices()))
			->add('listOrder', new NumberType, array('label' => 'Order'))
			->add('save', 'submit', array('label' => 'Save'));
	}

	public function getName()
	{
		return 'Link';
	}
	
	private function createChoices()
	{
		if ($this->type == self::PROJECT_SPECIFIC) {
			return [
				Link::PRESENT_PROJECT => Link::presentedToText(Link::PRESENT_PROJECT),
				Link::PRESENT_GROUP => Link::presentedToText(Link::PRESENT_GROUP),
				Link::PRESENT_AREA => Link::presentedToText(Link::PRESENT_AREA),
			];
		} else {
			return [
				Link::PRESENT_ADMIN => Link::presentedToText(Link::PRESENT_ADMIN),
				Link::PRESENT_USER => Link::presentedToText(Link::PRESENT_USER)
			];
		}
	}
}