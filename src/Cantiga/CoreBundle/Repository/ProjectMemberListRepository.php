<?php
namespace Cantiga\CoreBundle\Repository;

use Doctrine\DBAL\Connection;
use Exception;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Join;
use Cantiga\Metamodel\MembershipRoleResolver;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\QueryOperator;
use Cantiga\Metamodel\Transaction;

/**
 * Shows the list of members of the given project.
 *
 * @author Tomasz Jędrzejewski
 */
class ProjectMemberListRepository
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
	 * @var MembershipRoleResolver
	 */
	private $roleResolver;
	
	public function __construct(Connection $conn, Transaction $transaction, MembershipRoleResolver $roleResolver)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->roleResolver = $roleResolver;
	}
	
	/**
	 * @return DataTable
	 */
	public function createDataTable()
	{
		$dt = new DataTable();
		$dt->id('id', 'i.id')
			->searchableColumn('name', 'i.name')
			->column('location', 'p.location')
			->column('note', 'm.note');
		return $dt;
	}
	
	public function listData(Project $project, DataTable $dataTable)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.name', 'name')
			->field('p.location', 'location')
			->field('m.note', 'note')
			->from(CoreTables::USER_TBL, 'i')
			->join(CoreTables::USER_PROFILE_TBL, 'p', QueryClause::clause('p.`userId` = i.`id`'))
			->join(CoreTables::PROJECT_MEMBER_TBL, 'm', QueryClause::clause('m.`userId` = i.`id`'))
			->where(QueryClause::clause('m.projectId = :projectId AND i.active = 1', ':projectId', $project->getId()));	
		
		$countingQuery = QueryBuilder::select()
			->from(CoreTables::USER_TBL, 'i')
			->join(CoreTables::PROJECT_MEMBER_TBL, 'm', QueryClause::clause('m.`userId` = i.`id`'))
			->where(QueryClause::clause('m.projectId = :projectId AND i.active = 1', ':projectId', $project->getId()));
		
		$recordsTotal = QueryBuilder::copyWithoutFields($countingQuery)
			->field('COUNT(id)', 'cnt')
			->where($dataTable->buildCountingCondition($qb->getWhere()))
			->fetchCell($this->conn);
		$recordsFiltered = QueryBuilder::copyWithoutFields($countingQuery)
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
	 * @param $project Current project
	 * @param $id User ID
	 * @return User
	 */
	public function getItem(Project $project, $id)
	{
		$this->transaction->requestTransaction();
		try {
			$user = User::fetchLinkedProfile($this->conn, $this->roleResolver, $project,
				Join::create(CoreTables::PROJECT_MEMBER_TBL, 'm', QueryClause::clause('m.userId = u.id')),
				QueryOperator::op('AND')
					->expr(QueryClause::clause('m.projectId = :projectId', ':projectId', $project->getId()))
					->expr(QueryClause::clause('u.`id` = :userId', ':userId', $id)));
			
			if (false === $user) {
				throw new ItemNotFoundException('The specified user has not been found.');
			}
			return $user;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
}
