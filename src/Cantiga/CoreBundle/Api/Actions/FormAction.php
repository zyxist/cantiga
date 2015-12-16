<?php
namespace Cantiga\CoreBundle\Api\Actions;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Controller\RedirectHandlingInterface;
use Cantiga\Metamodel\CustomForm\CustomFormModelInterface;
use Cantiga\Metamodel\Exception\ModelException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Similar to {@link EditAction}, but more general. The entity must be provided directly,
 * and there is no default action after form processing.
 *
 * @author Tomasz Jędrzejewski
 */
class FormAction extends AbstractAction
{
	private $item;
	private $formOperation;
	private $customForm;
	private $formBuilder;
	
	private $action;
	private $redirect;
	private $formSubmittedMessage;
	private $template;
	
	public function __construct($item, AbstractType $formType)
	{
		$this->item = $item;
		$this->formBuilder = function($controller, $item, $action) use($formType) {
			return $controller->createForm($formType, $item, array('action' => $action));
		};
	}
	
	public function action($action)
	{
		$this->action = $action;
		return $this;
	}
	
	public function formSubmittedMessage($formSubmittedMessage)
	{
		$this->formSubmittedMessage = $formSubmittedMessage;
		return $this;
	}
	
	public function onSubmit($callback)
	{
		$this->formOperation = $callback;
		return $this;
	}
	
	public function redirect($url)
	{
		$this->redirect = $url;
		return $this;
	}
	
	public function template($tpl)
	{
		$this->template = $tpl;
		return $this;
	}
	
	public function form($callback)
	{
		$this->formBuilder = $callback;
		return $this;
	}
	
	public function customForm(CustomFormModelInterface $customForm)
	{
		$this->customForm = $customForm;
		return $this;
	}
	
	public function run(CantigaController $controller, Request $request)
	{
		try {
			$call = $this->formBuilder;
			$form = $call($controller, $this->item, $this->action);
			$form->handleRequest($request);
			
			if ($form->isValid()) {
				$call = $this->formOperation;
				$call($this->item);
				$controller->get('session')->getFlashBag()->add('info', $controller->trans($this->formSubmittedMessage));
				return $controller->redirect($this->redirect);
			}
			$vars = $this->getVars();
			$vars['item'] = $this->item;
			$vars['form'] = $form->createView();
			$vars['user'] = $controller->getUser();
			$vars['formRenderer'] = (null !== $this->customForm ? $this->customForm->createFormRenderer() : null);
			
			return $controller->render($this->template, $vars);
		} catch(ModelException $exception) {
			return $this->onError($controller, $controller->trans($exception->getMessage()));
		}
	}
	
	public function onError($controller, $message)
	{
		if ($controller instanceof RedirectHandlingInterface) {
			return $controller->onError($message);
		}
		$controller->get('session')->getFlashBag()->add('error', $message);
		return $controller->redirect($this->redirect);
	}
}
