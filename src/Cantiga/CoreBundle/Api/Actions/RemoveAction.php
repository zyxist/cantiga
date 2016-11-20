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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\LogicException;
use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;

/**
 * Generic action for removing the entities.
 *
 * @author Tomasz Jędrzejewski
 */
class RemoveAction extends AbstractAction
{
	public function __construct(CRUDInfo $crudInfo)
	{
		$this->info = $crudInfo;
	}
	
	public function run(CantigaController $controller, $id, Request $request)
	{
		try {
			$repository = $this->info->getRepository();
			$item = $repository->getItem($id);
			
			$nameProperty = 'get'.ucfirst($this->info->getItemNameProperty());
			$name = $item->$nameProperty();
			
			if (!$item instanceof RemovableEntityInterface) {
				throw new LogicException('This entity does not support removing.');
			}
			
			if (!$item->canRemove()) {
				throw new ModelException($controller->trans($this->info->getCannotRemoveMessage(), [$name]));
			}
		
			$answer = $request->query->get('answer', null);
			
			if($answer == 'yes') {
				$repository->remove($item);
				return $this->onSuccess($controller, $controller->trans($this->info->getItemRemovedMessage(), [$item->$nameProperty()]));
			} elseif($answer == 'no') {
				return $this->toIndexPage($controller);
			} else {
				$controller->breadcrumbs()->link($name, $this->info->getInfoPage(), $this->slugify(['id' => $id]));
				$controller->breadcrumbs()->link($controller->trans('Remove', [], 'general'), $this->info->getRemovePage(), $this->slugify(['id' => $id]));
				
				$vars = $this->getVars();

				$vars['pageTitle'] = $this->info->getPageTitle();
				$vars['pageSubtitle'] = $this->info->getPageSubtitle();
				$vars['questionTitle'] = $controller->trans($this->info->getRemoveQuestionTitle());
				$vars['question'] = $controller->trans($this->info->getRemoveQuestion(), [$item->$nameProperty()]);
				$vars['successPath'] = $controller->generateUrl($this->info->getRemovePage(), $this->slugify(['id' => $id, 'answer' => 'yes']));
				$vars['failurePath'] = $controller->generateUrl($this->info->getRemovePage(), $this->slugify(['id' => $id, 'answer' => 'no']));
				$vars['successBtn'] = $controller->trans('Indeed, remove it', [], 'general');
				$vars['failureBtn'] = $controller->trans('Cancel', [], 'general');
				$vars['indexPage'] = $this->info->getIndexPage();
				$vars['infoPage'] = $this->info->getInfoPage();
				$vars['insertPage'] = $this->info->getInsertPage();
				$vars['editPage'] = $this->info->getEditPage();
				$vars['removePage'] = $this->info->getRemovePage();
				
				return $controller->render('CantigaCoreBundle:layout:question.html.twig', $vars);
			}
		} catch(ItemNotFoundException $exception) {
			return $this->onError($controller, $controller->trans($this->info->getItemNotFoundErrorMessage()));
		} catch(ModelException $exception) {
			return $this->onError($controller, $controller->trans($exception->getMessage()));
		}
	}
}
