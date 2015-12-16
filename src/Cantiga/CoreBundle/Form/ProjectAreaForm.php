<?php
namespace Cantiga\CoreBundle\Form;

use Cantiga\Metamodel\CustomForm\CustomFormEventSubscriber;
use Cantiga\Metamodel\CustomForm\CustomFormModelInterface;
use Cantiga\Metamodel\Form\EntityTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProjectAreaForm extends AbstractType
{
	/**
	 * @var CustomFormModelInterface
	 */
	private $customFormModel;
	private $territoryRepository;
	private $groupRepository;
	private $statusRepository;
	
	public function __construct(CustomFormModelInterface $customFormModel, $territoryRepository, $groupRepository, $statusRepository)
	{
		$this->customFormModel = $customFormModel;
		$this->territoryRepository = $territoryRepository;
		$this->groupRepository = $groupRepository;
		$this->statusRepository = $statusRepository;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', 'text', array('label' => 'Name'))
			->add('territory', 'choice', array('label' => 'Territory', 'choices' => $this->territoryRepository->getFormChoices()))
			->add('group', 'choice', array('label' => 'Group', 'choices' => $this->groupRepository->getFormChoices()))
			->add('status', 'choice', array('label' => 'Status', 'choices' => $this->statusRepository->getFormChoices()))
			->add('save', 'submit', array('label' => 'Save'));
		$builder->get('territory')->addModelTransformer(new EntityTransformer($this->territoryRepository));
		$builder->get('group')->addModelTransformer(new EntityTransformer($this->groupRepository));
		$builder->get('status')->addModelTransformer(new EntityTransformer($this->statusRepository));
		$builder->addEventSubscriber(new CustomFormEventSubscriber($this->customFormModel));
	}

	public function getName()
	{
		return 'Area';
	}
}