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
namespace Cantiga\CourseBundle\Repository;

use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CourseBundle\CourseTables;
use Cantiga\CourseBundle\Entity\Course;
use Cantiga\CourseBundle\Entity\CourseTest;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;

class ProjectCourseRepository
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
	 * @var Project
	 */
	private $project;
	
	public function __construct(Connection $conn, Transaction $transaction)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
	}
	
	public function setProject(Project $project)
	{
		$this->project = $project;
	}
	
	/**
	 * @return DataTable
	 */
	public function createDataTable()
	{
		$dt = new DataTable();
		$dt->id('id', 'i.id')
			->searchableColumn('name', 'i.name')
			->column('lastUpdated', 'i.lastUpdated')
			->column('isPublished', 'i.isPublished')
			->column('deadline', 'i.deadline')
			->column('displayOrder', 'i.displayOrder');
		return $dt;
	}
	
	public function listData(DataTable $dataTable, TimeFormatterInterface $timeFormatter)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.name', 'name')
			->field('i.lastUpdated', 'lastUpdated')
			->field('i.isPublished', 'isPublished')
			->field('i.deadline', 'deadline')
			->field('i.displayOrder', 'displayOrder')
			->from(CourseTables::COURSE_TBL, 'i')
			->where(QueryClause::clause('i.`projectId` = :projectId', ':projectId', $this->project->getId()));

		$qb->postprocess(function($row) use($timeFormatter) {
			$row['lastUpdatedAgo'] = $timeFormatter->ago($row['lastUpdated']);
			if (!empty($row['deadline'])) {
				$row['deadlineDate'] = $timeFormatter->format(TimeFormatterInterface::FORMAT_DATE_LONG, $row['deadline']);
			} else {
				$row['deadlineDate'] = '---';
			}
			return $row;
		});
		
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
	 * @return Course
	 */
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		$item = Course::fetchByProject($this->conn, $id, $this->project);
		
		if(false === $item) {
			$this->transaction->requestRollback();
			throw new ItemNotFoundException('The specified item has not been found.', $id);
		}
		return $item;
	}
	
	public function insert(Course $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->insert($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function saveTest(Course $item)
	{
		$this->transaction->requestTransaction();
		try {
			if ($item->hasTest()) {
				$item->getTest()->save($this->conn);
			}
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function update(Course $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->update($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function remove(Course $item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->remove($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function importFrom(HierarchicalInterface $source, HierarchicalInterface $destination)
	{
		$this->transaction->requestTransaction();
		try {
			$sourceCourse = $this->conn->fetchAll('SELECT t.*, c.* FROM `'.CourseTables::COURSE_TBL.'` c '
				. 'LEFT JOIN `'.CourseTables::COURSE_TEST_TBL.'` t ON t.`courseId` = c.`id` '
				. 'WHERE c.`projectId` = :sourceProjectId FOR UPDATE', [':sourceProjectId' => $source->getId()]);
			$destinationCourse = $this->conn->fetchAll('SELECT `presentationLink` FROM `'.CourseTables::COURSE_TBL.'` WHERE `projectId` = :dstProjectId FOR UPDATE', [':dstProjectId' => $destination->getId()]);
			$set = [];
			foreach ($destinationCourse as $row) {
				$set[$row['presentationLink']] = true;
			}
			foreach ($sourceCourse as $course) {
				if (!isset($set[$course['presentationLink']])) {
					$item = new Course();
					$item->setProject($destination);
					$item->setName($course['name']);
					$item->setDescription($course['description']);
					$item->setDeadline($course['deadline']);
					$item->setPresentationLink($course['presentationLink']);
					$item->setAuthorName($course['authorName']);
					$item->setAuthorEmail($course['authorEmail']);
					$item->setNotes($course['notes']);
					$item->setIsPublished(false);
					$id = $item->insert($this->conn);
					
					if (!empty($course['testStructure'])) {
						$test = new CourseTest($item, $course['testStructure']);
						$test->save($this->conn);
					}
				}
			}
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
}