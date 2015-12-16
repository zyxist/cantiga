<?php
namespace Cantiga\CoreBundle\Form;

use Cantiga\CoreBundle\Api\AppTexts;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AppTextForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('place', new ChoiceType, array('label' => 'Place', 'choices' => AppTexts::getNames(), 'attr' => array('help_text' => 'Place where the text is displayed.')))
			->add('title', new TextType, array('label' => 'Title', 'attr' => array('help_text' => 'Some places do not need to show any title.')))
			->add('content', new TextareaType, array('label' => 'Content', 'attr' => ['rows' => 20]))
			->add('locale', new TextType, array('label' => 'Locale', 'attr' => array('help_text' => 'Must match one of the installed languages.')))
			->add('save', new SubmitType, array('label' => 'Save'));
	}

	public function getName()
	{
		return 'AppText';
	}
}