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
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;


/**
 * Generic action for printing the summary about an entity.
 * 
 * @author Tomasz Jędrzejewski
 */
class InfoAction extends AbstractAction
{
	private $fetch;
	
	public function __construct(CRUDInfo $crudInfo)
	{
		$this->info = $crudInfo;
		$this->fetch = function($repository, $id) {
			return $repository->getItem($id);
		};
	}
	
	/**
	 * Sets the custom function for fetching the data. The function must accept two
	 * arguments: repository and the ID.
	 * 
	 * @param callback $callable
	 * @return Cantiga\CoreBundle\Api\Actions\InfoAction
	 */
	public function fetch($callable)
	{
		$this->fetch = $callable;
		return $this;
	}
	
	public function run(CantigaController $controller, $id, $customDataGenerator = null)
	{
		try {
			$repository = $this->info->getRepository();
			$fetch = $this->fetch;
			$item = $fetch($repository, $id);
			
			$nameProperty = 'get'.ucfirst($this->info->getItemNameProperty());
			$name = $item->$nameProperty();
			
			$customData = null;
			if(is_callable($customDataGenerator)) {
				$customData = $customDataGenerator($item);
			}
			
			$vars = $this->getVars();
			$vars['pageTitle'] = $this->info->getPageTitle();
			$vars['pageSubtitle'] = $this->info->getPageSubtitle();
			$vars['item'] = $item;
			$vars['name'] = $name;
			$vars['custom'] = $customData;
			$vars['indexPage'] = $this->info->getIndexPage();
			$vars['infoPage'] = $this->info->getInfoPage();
			$vars['insertPage'] = $this->info->getInsertPage();
			$vars['editPage'] = $this->info->getEditPage();
			$vars['removePage'] = $this->info->getRemovePage();
			
			$controller->breadcrumbs()->link($name, $this->info->getInfoPage(), $this->slugify(['id' => $id]));
			
			return $controller->render($this->info->getTemplateLocation().$this->info->getInfoTemplate(), $vars);
		} catch(ItemNotFoundException $exception) {
			return $this->onError($controller, $controller->trans($this->info->getItemNotFoundErrorMessage()));
		} catch(ModelException $exception) {
			return $this->onError($controller, $controller->trans($exception->getMessage()));
		}
	}
}
