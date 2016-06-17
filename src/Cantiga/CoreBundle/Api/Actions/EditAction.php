<?php
namespace Cantiga\CoreBundle\Api\Actions;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\CustomForm\CustomFormModelInterface;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\LogicException;

/**
 * Generic action for handling the form to update entitites.
 * 
 * @author Tomasz Jędrzejewski
 */
class EditAction extends AbstractAction
{
	private $updateOperation;
	private $formType;
	private $customForm;
	private $formBuilder;
	
	public function __construct(CRUDInfo $crudInfo, $formType = null, array $options = [])
	{
		$this->info = $crudInfo;
		$this->updateOperation = function($repository, $item) {
			$repository->update($item);
		};
		$this->formBuilder = function($controller, $item, $formType, $action) use($formType, $options) {
			return $controller->createForm($formType, $item, array_merge(['action' => $action], $options));
		};
	}
	
	public function update($callback)
	{
		$this->updateOperation = $callback;
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
	
	public function run(CantigaController $controller, $id, Request $request)
	{
		try {
			$repository = $this->info->getRepository();
			$item = $repository->getItem($id);
			
			$nameProperty = 'get'.ucfirst($this->info->getItemNameProperty());
			$name = $item->$nameProperty();
					
			if (!$item instanceof EditableEntityInterface) {
				throw new LogicException('This entity does not support editing.');
			}
			$call = $this->formBuilder;
			$form = $call($controller, $item, $this->formType, $controller->generateUrl($this->info->getEditPage(), $this->slugify(['id' => $id])));
			$form->handleRequest($request);
			
			if ($form->isValid()) {
				$call = $this->updateOperation;
				$call($repository, $item);
				$controller->get('session')->getFlashBag()->add('info', $controller->trans($this->info->getItemUpdatedMessage(), [$name]));
				return $controller->redirect($controller->generateUrl($this->info->getInfoPage(), $this->slugify(['id' => $id])));
			}
			$controller->breadcrumbs()->link($name, $this->info->getInfoPage(), $this->slugify(['id' => $id]));
			if ($this->hasBreadcrumbs()) {
				$controller->breadcrumbs()->item($this->breadcrumbs);
			} else {
				$controller->breadcrumbs()->link($controller->trans('Edit', [], 'general'), $this->info->getEditPage(), $this->slugify(['id' => $id]));
			}
			
			$vars = $this->getVars();
			$vars['pageTitle'] = $this->info->getPageTitle();
			$vars['pageSubtitle'] = $this->info->getPageSubtitle();
			$vars['item'] = $item;
			$vars['form'] = $form->createView();
			$vars['user'] = $controller->getUser();
			$vars['formRenderer'] = (null !== $this->customForm ? $this->customForm->createFormRenderer() : null);
			$vars['indexPage'] = $this->info->getIndexPage();
			$vars['infoPage'] = $this->info->getInfoPage();
			$vars['insertPage'] = $this->info->getInsertPage();
			$vars['editPage'] = $this->info->getEditPage();
			$vars['removePage'] = $this->info->getRemovePage();
			
			return $controller->render($this->info->getTemplateLocation().'edit.html.twig', $vars);
		} catch(ItemNotFoundException $exception) {
			return $this->onError($controller, $controller->trans($this->info->getItemNotFoundErrorMessage()));
		} catch(ModelException $exception) {
			return $this->onError($controller, $controller->trans($exception->getMessage()));
		}
	}
}
