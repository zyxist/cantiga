<?php
namespace Cantiga\CoreBundle\Api\Actions;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;


/**
 * Description of InfoAction
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
