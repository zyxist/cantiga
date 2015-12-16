<?php
namespace Cantiga\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Cantiga\CoreBundle\Api\Modules;
use Cantiga\CoreBundle\Form\Type\BooleanType;
use Cantiga\CoreBundle\Repository\ArchivedProjectRepository;

class AdminProjectForm extends AbstractType
{
	private $projectRepo;
	
	public function __construct(ArchivedProjectRepository $projectRepo)
	{
		$this->projectRepo = $projectRepo;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', 'text', array('label' => 'Name'))
			->add('description', 'textarea', array('label' => 'Description'))
			->add('parentProject', 'choice', array('label' => 'Parent project', 'required' => false, 'choices' => $this->projectRepo->getFormChoices()))
			->add('modules', 'choice', array('label' => 'Modules', 'expanded' => true, 'multiple' => true, 'choices' => Modules::getFormEntries()))
			->add('areasAllowed', new BooleanType(), array('label' => 'Areas allowed?'))
			->add('areaRegistrationAllowed', new BooleanType(), array('label' => 'Area registration allowed?'))
			->add('save', 'submit', array('label' => 'Save'));
	}

	public function getName()
	{
		return 'Project';
	}
}