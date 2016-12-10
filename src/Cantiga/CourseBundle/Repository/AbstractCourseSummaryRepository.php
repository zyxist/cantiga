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

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CourseBundle\CourseTables;
use Cantiga\CourseBundle\Entity\Course;
use Cantiga\CourseBundle\Entity\TestResult;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\Transaction;
use Cantiga\UserBundle\UserTables;
use Doctrine\DBAL\Connection;

/**
 * @author Tomasz Jędrzejewski
 */
abstract class AbstractCourseSummaryRepository
{
	/**
	 * @var Connection 
	 */
	protected $conn;
	/**
	 * @var Transaction
	 */
	protected $transaction;
	/**
	 * @var Project
	 */
	protected $project;
	/**
	 * @var Group
	 */
	protected $group;
	
	public function __construct(Connection $conn, Transaction $transaction)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
	}
	
	public function setProject(Project $project)
	{
		$this->project = $project;
	}
	
	public function setGroup(Group $group)
	{
		$this->group = $group;
	}
	
	/**
	 * @return DataTable
	 */
	public function createDataTable()
	{
		$dt = new DataTable();
		$dt->id('id', 'i.id')
			->searchableColumn('name', 'i.name')
			->column('passedCourseNum', 'r.passedCourseNum')
			->column('failedCourseNum', 'r.failedCourseNum')
			->column('progress', 'r.passedCourseNum');
		return $dt;
	}
	
	public function listData(DataTable $dataTable)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.name', 'name')
			->field('r.mandatoryCourseNum', 'mandatoryCourseNum')
			->field('r.passedCourseNum', 'passedCourseNum')
			->field('r.failedCourseNum', 'failedCourseNum')
			->from(CoreTables::AREA_TBL, 'i')
			->join(CourseTables::COURSE_PROGRESS_TBL, 'r', QueryClause::clause('r.areaId = i.id'))
			->leftJoin(CoreTables::GROUP_TBL, 'g', QueryClause::clause('g.id = i.groupId'));
		if (null !== $this->group) {
			$qb->where(QueryClause::clause('i.`groupId` = :groupId', ':groupId', $this->group->getId()));
		} else {
			$qb->where(QueryClause::clause('i.`projectId` = :projectId', ':projectId', $this->project->getId()));
		}
		
		$recordsTotal = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(i.id)', 'cnt')
			->where($dataTable->buildCountingCondition($qb->getWhere()))
			->fetchCell($this->conn);
		$recordsFiltered = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(i.id)', 'cnt')
			->where($dataTable->buildFetchingCondition($qb->getWhere()))
			->fetchCell($this->conn);
		$qb->postprocess(function($row) {
			if ($row['mandatoryCourseNum'] == 0) {
				$row['progress'] = 0;
			} else {
				$row['progress'] = $row['passedCourseNum'] / $row['mandatoryCourseNum'] * 100;
			}
			return $row;
		});
		
		$dataTable->processQuery($qb);
		return $dataTable->createAnswer(
			$recordsTotal,
			$recordsFiltered,
			$qb->where($dataTable->buildFetchingCondition($qb->getWhere()))->fetchAll($this->conn)
		);
	}
	
	public function findTotalIndividualResultsForArea(Area $area)
	{
		$items = $this->conn->fetchAll('SELECT c.`id` AS `courseId`, c.`name` AS `courseName`, u.`id` AS `userId`, u.`name` AS `userName`, u.`avatar`, '
			. 'ur.`result`, ur.`totalQuestions`, ur.`passedQuestions`, ur.`completedAt`, ur.`trialNumber` '
			. 'FROM `'.CourseTables::COURSE_TBL.'` c '
			. 'INNER JOIN `'.UserTables::PLACE_MEMBERS_TBL.'` m ON m.`placeId` = :placeId '
			. 'INNER JOIN `'.CoreTables::USER_TBL.'` u ON u.`id` = m.`userId` '
			. 'LEFT JOIN `'.CourseTables::COURSE_RESULT_TBL.'` ur ON ur.`userId` = u.`id` AND c.`id` = ur.`courseId` '
			. 'WHERE c.`isPublished` = 1 AND u.`active` = 1 AND c.`projectId` = :projectId '
			. 'ORDER BY c.`displayOrder`, u.`name`', [':placeId' => $area->getPlace()->getId(), ':projectId' => $area->getProject()->getId()]);
		foreach ($items as &$item) {
			TestResult::processResults($item);
		}
		return $items;
	}
	
	/**
	 * @return Course
	 */
	public function getCourse($id)
	{
		$this->transaction->requestTransaction();
		$item = Course::fetchByProject($this->conn, $id, $this->project);
		
		if(false === $item) {
			$this->transaction->requestRollback();
			throw new ItemNotFoundException('The specified item has not been found.', $id);
		}
		return $item;
	}
}
