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
declare(strict_types=1);
namespace Cantiga\CoreBundle\Api\Actions;

/**
 * Keep the entire information used by CRUD actions in a single place, so that it is kept in
 * single place in the controller, so that it is easy to find and edit it.
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

	public function setTemplateLocation(string $templateLocation): self
	{
		$this->templateLocation = $templateLocation;
		return $this;
	}

	public function setItemNameProperty(string $itemNameProperty): self
	{
		$this->itemNameProperty = $itemNameProperty;
		return $this;
	}

	public function setIndexPage(string $indexPage): self
	{
		$this->indexPage = $indexPage;
		return $this;
	}

	public function setInfoPage(string $infoPage): self
	{
		$this->infoPage = $infoPage;
		return $this;
	}

	public function setInsertPage(string $page): self
	{
		$this->insertPage = $page;
		return $this;
	}

	public function setEditPage(string $editPage): self
	{
		$this->editPage = $editPage;
		return $this;
	}

	public function setRemovePage(string $removePage): self
	{
		$this->removePage = $removePage;
		return $this;
	}

	public function setErrorPage(string $errorPage): self
	{
		$this->errorPage = $errorPage;
		return $this;
	}

	public function setItemCreatedMessage(string $itemCreatedMessage): self
	{
		$this->itemCreatedMessage = $itemCreatedMessage;
		return $this;
	}

	public function setItemUpdatedMessage(string $itemUpdatedMessage): self
	{
		$this->itemUpdatedMessage = $itemUpdatedMessage;
		return $this;
	}

	public function setItemRemovedMessage(string $itemRemovedMessage): self
	{
		$this->itemRemovedMessage = $itemRemovedMessage;
		return $this;
	}

	public function setItemNotFoundErrorMessage(string $itemNotFoundErrorMessage): self
	{
		$this->itemNotFoundErrorMessage = $itemNotFoundErrorMessage;
		return $this;
	}

	public function setIndexTemplate(string $indexTemplate): self
	{
		$this->indexTemplate = $indexTemplate;
		return $this;
	}

	public function setInfoTemplate(string $infoTemplate): self
	{
		$this->infoTemplate = $infoTemplate;
		return $this;
	}
	
	public function getRemoveQuestionTitle(): string
	{
		return $this->removeQuestionTitle;
	}

	public function getRemoveQuestion(): string
	{
		return $this->removeQuestion;
	}

	public function setRemoveQuestionTitle(string $removeQuestionTitle): self
	{
		$this->removeQuestionTitle = $removeQuestionTitle;
		return $this;
	}

	public function setRemoveQuestion(string $removeQuestion): self
	{
		$this->removeQuestion = $removeQuestion;
		return $this;
	}

	public function getPageTitle(): string
	{
		return $this->pageTitle;
	}

	public function getPageSubtitle(): string
	{
		return $this->pageSubtitle;
	}

	public function setPageTitle(string $pageTitle): self
	{
		$this->pageTitle = $pageTitle;
		return $this;
	}

	public function setPageSubtitle(string $pageSubtitle): self
	{
		$this->pageSubtitle = $pageSubtitle;
		return $this;
	}
	
	public function getCannotRemoveMessage(): string
	{
		return $this->cannotRemoveMessage;
	}

	public function setCannotRemoveMessage($cannotRemoveMessage): self
	{
		$this->cannotRemoveMessage = $cannotRemoveMessage;
		return $this;
	}


}
