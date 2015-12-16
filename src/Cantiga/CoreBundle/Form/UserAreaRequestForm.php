<?php
namespace Cantiga\CoreBundle\Form;

use Cantiga\CoreBundle\CoreSettings;
use Cantiga\CoreBundle\Repository\ProjectTerritoryRepository;
use Cantiga\CoreBundle\Settings\ProjectSettings;
use Cantiga\Metamodel\CustomForm\CustomFormEventSubscriber;
use Cantiga\Metamodel\CustomForm\CustomFormModelInterface;
use Cantiga\Metamodel\Form\EntityTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserAreaRequestForm extends AbstractType
{
	/**
	 * @var ProjectSettings 
	 */
	private $projectSettings;
	/**
	 * @var CustomFormModelInterface
	 */
	private $customFormModel;
	/**
	 * @var ProjectTerritoryRepository
	 */
	private $territoryRepository;
	
	public function __construct(ProjectSettings $settings, CustomFormModelInterface $customFormModel, ProjectTerritoryRepository $territoryRepository)
	{
		$this->projectSettings = $settings;
		$this->customFormModel = $customFormModel;
		$this->territoryRepository = $territoryRepository;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$hint = $this->projectSettings->get(CoreSettings::AREA_NAME_HINT)->getValue();
		
		$builder
			->add('name', 'text', array('label' => 'Area name', 'attr' => ['help_text' => $hint]))
			->add('territory', 'choice', array('label' => 'Territory', 'choices' => $this->territoryRepository->getFormChoices()))
			->add('save', 'submit', array('label' => 'Submit request'));
		$builder->get('territory')->addModelTransformer(new EntityTransformer($this->territoryRepository));
		$builder->addEventSubscriber(new CustomFormEventSubscriber($this->customFormModel));
	}

	public function getName()
	{
		return 'AreaRequest';
	}
}