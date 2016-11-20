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
namespace Cantiga\CoreBundle\Repository;

use Doctrine\DBAL\Connection;
use Exception;
use PDO;
use Symfony\Component\HttpFoundation\Request;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\AppText;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Form\ProjectForm;
use Cantiga\Metamodel\CustomForm\CustomFormBuilderInterface;
use Cantiga\Metamodel\CustomForm\CustomFormModel;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\Transaction;

/**
 * Customizable, project-specific form definitions. The form structure is specified in JSON and can be changed
 * directly from the panel.
 *
 * @author Tomasz Jędrzejewski
 */
class ProjectFormRepository
{
	/**
	 * @var Connection 
	 */
	private $conn;
	/**
	 * @var Transaction
	 */
	private $transaction;
	/**
	 * @var CustomFormBuilderInterface
	 */
	private $customFormBuilder;
	
	public function __construct(Connection $conn, Transaction $transaction, CustomFormBuilderInterface $customFormBuilder)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->customFormBuilder = $customFormBuilder;
	}
	
	/**
	 * @return DataTable
	 */
	public function createDataTable()
	{
		$dt = new DataTable();
		$dt->id('id', 'i.id')
			->searchableColumn('place', 'i.place')
			->searchableColumn('name', 'i.name')
			->column('locale', 'i.locale')
			->column('lastVersion', 'i.lastVersion');
		return $dt;
	}
	
	public function listData(DataTable $dataTable)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.place', 'place')
			->field('i.title', 'title')
			->field('i.locale', 'locale')
			->field('i.lastVersion', 'lastVersion')
			->from(CoreTables::FORM_TBL, 'i');	
		
		$recordsTotal = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(id)', 'cnt')
			->where($dataTable->buildCountingCondition($qb->getWhere()))
			->fetchCell($this->conn);
		$recordsFiltered = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(id)', 'cnt')
			->where($dataTable->buildFetchingCondition($qb->getWhere()))
			->fetchCell($this->conn);

		$dataTable->processQuery($qb);
		return $dataTable->createAnswer(
			$recordsTotal,
			$recordsFiltered,
			$qb->where($dataTable->buildFetchingCondition($qb->getWhere()))->fetchAll($this->conn)
		);
	}
	
	/**
	 * @return AppText
	 */
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		$item = ProjectForm::fetchById($this->conn, $id);
		if(null === $item) {
			$this->transaction->requestRollback();
			throw new ItemNotFoundException('The specified item has not been found.', $id);
		}
		return $item;
	}
	
	/**
	 * Fetches the custom form metadata associated with the given place, from the given project.
	 * The metadata are located by the locale settings specified in the request.
	 * 
	 * @param Project $project Project to load the form from
	 * @param string $place Name of the place, where the text is shown
	 * @param Cantiga\CoreBundle\Repository\Request $request Request is used to obtain the locale
	 * @throws ItemNotFoundException Form not found.
	 */
	public function getForm(Project $project, $place, Request $request)
	{
		$locale = $request->getLocale();
		$this->transaction->requestTransaction();
		try {
			$item = ProjectForm::fetchByProject($this->conn, $project, $place, $locale);
			if(false === $item) {
				throw new ItemNotFoundException('Form not found.');
			}
			return $item;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	/**
	 * Fetches the custom form model associated with the given place, from the given project.
	 * The metadata are located by the locale settings specified in the request.
	 * 
	 * @param Project $project Project to load the form from
	 * @param string $place Name of the place, where the text is shown
	 * @param Cantiga\CoreBundle\Repository\Request $request Request is used to obtain the locale
	 * @return CustomFormModel
	 * @throws ItemNotFoundException Form not found.
	 */
	public function getFormModel(Project $project, $place, Request $request)
	{
		$projectForm = $this->getForm($project, $place, $request);
		
		$model = new CustomFormModel($projectForm);
		$this->customFormBuilder->buildFormModel($model, $projectForm->getContent());
		return $model;
	}
	
	public function insert(ProjectForm $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->insert($this->conn);
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
		}
	}
	
	public function update(ProjectForm $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->update($this->conn);
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
		}
	}
	
	public function remove(ProjectForm $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->remove($this->conn);
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
		}
	}
	
	public function getFormChoices()
	{
		$this->transaction->requestTransaction();
		$stmt = $this->conn->query('SELECT `id`, `name` FROM `'.CoreTables::FORM_TBL.'` ORDER BY `name`');
		$result = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$result[$row['id']] = $row['name'];
		}
		$stmt->closeCursor();
		return $result;
	}

	public function transformToEntity($key)
	{
		return $this->getItem($key);
	}

	public function transformToKey($entity)
	{
		return $entity->getId();
	}

}
