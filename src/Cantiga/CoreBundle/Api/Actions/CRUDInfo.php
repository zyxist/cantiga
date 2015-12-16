<?php
namespace Cantiga\CoreBundle\Api\Actions;

/**
 * Keep the entire information used by CRUD actions in a single place, so that it is kept in
 * single place in the controller, so that it is easy to find and edit it.
 *
 * @author Tomasz Jędrzejewski
 */
class CRUDInfo
{
	private $repository;
	private $templateLocation;
	private $itemNameProperty;
	
	private $pageTitle;
	private $pageSubtitle;

	private $indexPage;
	private $infoPage;
	private $insertPage;
	private $editPage;
	private $removePage;
	private $errorPage;
	
	private $removeQuestionTitle = 'You are about to remove something!';
	private $removeQuestion = 'Do you really want to remove \'0\' item?';
	
	private $itemCreatedMessage = 'The item \'0\' has been created.';
	private $itemUpdatedMessage = 'The item \'0\' has been updated.';
	private $itemRemovedMessage = 'The item \'0\' has been removed.';
	private $itemNotFoundErrorMessage = 'The requested item cannot be found.';
	private $cannotRemoveMessage = 'The item \'0\' cannot be removed.';
	
	private $indexTemplate = 'index.html.twig';
	private $infoTemplate = 'info.html.twig';
	
	public function getRepository()
	{
		return $this->repository;
	}

	public function getTemplateLocation()
	{
		return $this->templateLocation;
	}

	public function getItemNameProperty()
	{
		return $this->itemNameProperty;
	}

	public function getIndexPage()
	{
		return $this->indexPage;
	}

	public function getInfoPage()
	{
		return $this->infoPage;
	}

	public function getInsertPage()
	{
		return $this->insertPage;
	}

	public function getEditPage()
	{
		return $this->editPage;
	}

	public function getRemovePage()
	{
		return $this->removePage;
	}

	public function getErrorPage()
	{
		return $this->errorPage;
	}

	public function getItemCreatedMessage()
	{
		return $this->itemCreatedMessage;
	}

	public function getItemUpdatedMessage()
	{
		return $this->itemUpdatedMessage;
	}

	public function getItemRemovedMessage()
	{
		return $this->itemRemovedMessage;
	}

	public function getItemNotFoundErrorMessage()
	{
		return $this->itemNotFoundErrorMessage;
	}

	public function getIndexTemplate()
	{
		return $this->indexTemplate;
	}

	public function getInfoTemplate()
	{
		return $this->infoTemplate;
	}

	public function setRepository($repository)
	{
		$this->repository = $repository;
		return $this;
	}

	public function setTemplateLocation($templateLocation)
	{
		$this->templateLocation = $templateLocation;
		return $this;
	}

	public function setItemNameProperty($itemNameProperty)
	{
		$this->itemNameProperty = $itemNameProperty;
		return $this;
	}

	public function setIndexPage($indexPage)
	{
		$this->indexPage = $indexPage;
		return $this;
	}

	public function setInfoPage($infoPage)
	{
		$this->infoPage = $infoPage;
		return $this;
	}

	public function setInsertPage($page)
	{
		$this->insertPage = $page;
		return $this;
	}

	public function setEditPage($editPage)
	{
		$this->editPage = $editPage;
		return $this;
	}

	public function setRemovePage($removePage)
	{
		$this->removePage = $removePage;
		return $this;
	}

	public function setErrorPage($errorPage)
	{
		$this->errorPage = $errorPage;
		return $this;
	}

	public function setItemCreatedMessage($itemCreatedMessage)
	{
		$this->itemCreatedMessage = $itemCreatedMessage;
		return $this;
	}

	public function setItemUpdatedMessage($itemUpdatedMessage)
	{
		$this->itemUpdatedMessage = $itemUpdatedMessage;
		return $this;
	}

	public function setItemRemovedMessage($itemRemovedMessage)
	{
		$this->itemRemovedMessage = $itemRemovedMessage;
		return $this;
	}

	public function setItemNotFoundErrorMessage($itemNotFoundErrorMessage)
	{
		$this->itemNotFoundErrorMessage = $itemNotFoundErrorMessage;
		return $this;
	}

	public function setIndexTemplate($indexTemplate)
	{
		$this->indexTemplate = $indexTemplate;
		return $this;
	}

	public function setInfoTemplate($infoTemplate)
	{
		$this->infoTemplate = $infoTemplate;
		return $this;
	}
	
	public function getRemoveQuestionTitle()
	{
		return $this->removeQuestionTitle;
	}

	public function getRemoveQuestion()
	{
		return $this->removeQuestion;
	}

	public function setRemoveQuestionTitle($removeQuestionTitle)
	{
		$this->removeQuestionTitle = $removeQuestionTitle;
		return $this;
	}

	public function setRemoveQuestion($removeQuestion)
	{
		$this->removeQuestion = $removeQuestion;
		return $this;
	}

	public function getPageTitle()
	{
		return $this->pageTitle;
	}

	public function getPageSubtitle()
	{
		return $this->pageSubtitle;
	}

	public function setPageTitle($pageTitle)
	{
		$this->pageTitle = $pageTitle;
		return $this;
	}

	public function setPageSubtitle($pageSubtitle)
	{
		$this->pageSubtitle = $pageSubtitle;
		return $this;
	}
	
	public function getCannotRemoveMessage()
	{
		return $this->cannotRemoveMessage;
	}

	public function setCannotRemoveMessage($cannotRemoveMessage)
	{
		$this->cannotRemoveMessage = $cannotRemoveMessage;
		return $this;
	}


}
