<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga contributors.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
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
	
	public function __construct($item, $formType, array $options = array())
	{
		$this->item = $item;
		$this->formBuilder = function($controller, $item, $action) use($formType, $options) {
			return $controller->createForm($formType, $item, array_merge(['action' => $action], $options));
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
