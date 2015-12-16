<?php

namespace Cantiga\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Cantiga\CoreBundle\Api\Workspaces;
use Cantiga\CoreBundle\Repository\LanguageRepository;
use Cantiga\Metamodel\Form\EntityTransformer;

class UserSettingsForm extends AbstractType
{
	private $langRepo;
	
	public function __construct(LanguageRepository $langRepo)
	{
		$this->langRepo = $langRepo;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('settingsLanguage', 'choice', array('label' => 'Site language', 'choices' => $this->langRepo->getFormChoices()))
			->add('settingsTimezone', 'timezone', array('label' => 'Timezone'))
			->add('save', 'submit', array('label' => 'Save'));
		$builder->get('settingsLanguage')->addModelTransformer(new EntityTransformer($this->langRepo));
	}

	public function getName()
	{
		return 'UserSettings';
	}
}