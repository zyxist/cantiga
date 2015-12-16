<?php
namespace Cantiga\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Cantiga\CoreBundle\Repository\LanguageRepository;
use Cantiga\Metamodel\Form\EntityTransformer;

class UserRegistrationForm extends AbstractType
{
	/**
	 * @var LanguageRepository
	 */
	private $languageRepository;
	
	public function __construct(LanguageRepository $repository)
	{
		$this->languageRepository = $repository;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', 'text', array('label' => 'Full name', 'attr' => ['placeholder' => 'Full name']))
			->add('login', 'text', array('label' => 'Login', 'attr' => ['placeholder' => 'Login']))
			->add('password', 'password', array('label' => 'Password', 'attr' => ['placeholder' => 'Password']))
			->add('repeatPassword', 'password', array('label' => 'Repeat password', 'attr' => ['placeholder' => 'Repeat password']))
			->add('email', 'email', array('label' => 'E-mail address', 'attr' => ['placeholder' => 'E-mail']))
			->add('language', 'choice', array('label' => 'Language', 'choices' => $this->languageRepository->getFormChoices()))
			->add('acceptRules', 'checkbox');
		
		$builder->get('language')->addModelTransformer(new EntityTransformer($this->languageRepository));
	}

	public function getName()
	{
		return 'UserRegistration';
	}
}