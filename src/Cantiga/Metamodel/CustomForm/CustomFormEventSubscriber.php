<?php
namespace Cantiga\Metamodel\CustomForm;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


/**
 * Description of CustomFormEventSubscriber
 *
 * @author Tomasz Jędrzejewski
 */
class CustomFormEventSubscriber implements EventSubscriberInterface
{
	private $customFormModel;
	
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }
	
	public function __construct(CustomFormModelInterface $customFormModel)
	{
		$this->customFormModel = $customFormModel;
	}
	
	public function preSetData(FormEvent $event)
	{
		$form = $event->getForm();
		$form->add('customData', new CustomFormType(function(FormBuilderInterface $builder) {
			$this->customFormModel->constructForm($builder);			
		}, [$this->customFormModel, 'validateForm']));
	}
}
