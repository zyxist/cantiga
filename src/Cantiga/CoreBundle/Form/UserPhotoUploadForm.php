<?php
namespace Cantiga\CoreBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


/**
 * @author Tomasz Jędrzejewski
 */
class UserPhotoUploadForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('photo', 'file', array('label' => 'New photo image'))
			->add('save', 'submit', array('label' => 'Send'));
	}

	public function getName()
	{
		return 'PhotoUpload';
	}
}
