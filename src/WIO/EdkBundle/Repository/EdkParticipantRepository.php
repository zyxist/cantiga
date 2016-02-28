<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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
namespace WIO\EdkBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\Capabilities\InsertableRepositoryInterface;
use Cantiga\Metamodel\Capabilities\MembershipEntityInterface;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\Statistics\StatDateDataset;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\Metamodel\Transaction;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;
use WIO\EdkBundle\EdkEvents;
use WIO\EdkBundle\EdkTables;
use WIO\EdkBundle\Entity\EdkAreaNotes;
use WIO\EdkBundle\Entity\EdkParticipant;
use WIO\EdkBundle\Entity\EdkRegistrationSettings;
use WIO\EdkBundle\Entity\WhereLearntAbout;
use WIO\EdkBundle\Event\RegistrationEvent;

/**
 * Manages the participants.
 *
 * @author Tomasz Jędrzejewski
 */
class EdkParticipantRepository implements InsertableRepositoryInterface
{
	const AVAILABILITY_TIME = 300;
	
	/**
	 * @var Connection 
	 */
	private $conn;
	/**
	 * @var Transaction
	 */
	private $transaction;
	/**
	 * @var EventDispatcherInterface
	 */
	private $eventDispatcher;
	/**
	 * @var TimeFormatterInterface
	 */
	private $timeFormatter;
	/**
	 * View participants of the given area.
	 * @var Area
	 */
	private $area;
	
	public function __construct(Connection $conn, Transaction $transaction, EventDispatcherInterface $eventDispatcher, TimeFormatterInterface $timeFormatter)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->eventDispatcher = $eventDispatcher;
		$this->timeFormatter = $timeFormatter;
	}
	
	public function setArea(Area $area)
	{
		$this->area = $area;
	}
	
	/**
	 * @return DataTable
	 */
	public function createDataTable()
	{
		$dt = new DataTable();
		$dt->id('id', 'i.id')
			->searchableColumn('firstName', 'i.firstName')
			->searchableColumn('lastName', 'i.lastName')
			->searchableColumn('routeName', 'r.name')
			->column('createdAt', 'i.createdAt')
			->column('age', 'i.age')
			->column('sex', 'i.sex');
		return $dt;
	}
	
	public function listData(DataTable $dataTable, TranslatorInterface $translator)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.firstName', 'firstName')
			->field('i.lastName', 'lastName')
			->field('r.id', 'routeId')
			->field('r.name', 'routeName')
			->field('i.createdAt', 'createdAt')
			->field('i.age', 'age')
			->field('i.sex', 'sex')
			->from(EdkTables::PARTICIPANT_TBL, 'i')
			->join(EdkTables::ROUTE_TBL, 'r', QueryClause::clause('i.routeId = r.id'))
			->where(QueryClause::clause('i.`areaId` = :areaId', ':areaId', $this->area->getId()))
			->orderBy('i.createdAt', 'DESC');

		$qb->postprocess(function($row) use ($translator) {
			$row['sexText'] = $row['sex'] == 1 ? $translator->trans('SexMale', [], 'edk') : $translator->trans('SexFemale', [], 'edk');
			$row['createdAt'] = $this->timeFormatter->ago($row['createdAt']);
			return $row;
		});
		
		$recordsTotal = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(i.id)', 'cnt')
			->where($dataTable->buildCountingCondition($qb->getWhere()))
			->fetchCell($this->conn);
		$recordsFiltered = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(i.id)', 'cnt')
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
	 * @return EdkParticipant
	 */
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		try {
			$item = EdkParticipant::fetchByArea($this->conn, $id, $this->area);
			if(false === $item) {
				$this->transaction->requestRollback();
				throw new ItemNotFoundException('The specified participant has not been found.', $id);
			}
			return $item;
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function getPublicRegistration($routeId, $expectedAreaStatus)
	{
		$this->transaction->requestTransaction();
		try {
			$item = EdkRegistrationSettings::fetchPublic($this->conn, $routeId, $expectedAreaStatus);
			if (false === $item) {
				throw new ItemNotFoundException('There is no registration for this route available.');
			}
			return $item;
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function register($entity, $ipAddr, $slug)
	{
		$this->transaction->requestTransaction();
		try {
			$previous = $this->conn->fetchAssoc('SELECT `id` FROM `'.EdkTables::PARTICIPANT_TBL.'` WHERE `ipAddress` = :ip AND `createdAt` > :time', [':ip' => ip2long($ipAddr), ':time' => time() - self::AVAILABILITY_TIME]);
			
			if (!empty($previous)) {
				throw new ModelException('You have already registered not so long ago from this computer. Please wait a few moments.');
			}
				
			$id = $entity->insert($this->conn);			
			$this->eventDispatcher->dispatch(EdkEvents::REGISTRATION_COMPLETED, new RegistrationEvent($entity, $slug));
			return $id;
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	/**
	 * Fetches a pair of two objects: participant and area notes, where the participant is registered for the route.
	 * The access key is used for finding the record.
	 * 
	 * @param string $accessKey Access key generated during registration
	 * @param int $expectedAreaStatus Status of the published areas.
	 * @return array
	 * @throws \WIO\EdkBundle\Repository\Exception
	 * @throws ItemNotFoundException
	 */
	public function getItemByKey($accessKey, $expectedAreaStatus)
	{
		$this->transaction->requestTransaction();
		try {
			$item = EdkParticipant::fetchByKey($this->conn, $accessKey, $expectedAreaStatus, false);
			if (false === $item) {
				throw new ItemNotFoundException('Participant not found.');
			}
			$notes = EdkAreaNotes::fetchNotes($this->conn, $item->getRegistrationSettings()->getRoute()->getArea());
			return [$item, $notes];
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function removeItemByKey($accessKey, $expectedAreaStatus)
	{
		$this->transaction->requestTransaction();
		try {
			$item = EdkParticipant::fetchByKey($this->conn, $accessKey, $expectedAreaStatus, true);
			if (false === $item) {
				throw new ItemNotFoundException('Participant not found.');
			}
			$item->remove($this->conn);		
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}

	public function insert($entity)
	{
		$this->transaction->requestTransaction();
		try {
			return $entity->insert($this->conn);
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function update($item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->update($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function remove($item)
	{
		$this->transaction->requestTransaction();
		try {
			return $item->remove($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	/**
	 * Generates a CSV files with the data of all the registered people. The method is intended to be used within
	 * <tt>StreamedResponse</tt>.
	 * 
	 * @param TranslatorInterface $trans For generating localized column names
	 * @param int $area Area to export
	 * @param int $route Route to export
	 */
	public function exportToCSVStream(TranslatorInterface $trans, $area, $route = null)
	{
		$routeSelection = '';
		if(null !== $route) {
			$routeSelection = ' AND p.`routeId` = :routeId ';
		}
		
		$stmt = $this->conn->prepare('SELECT p.`firstName`, p.`lastName`, p.`sex`, p.`age`, p.`email`, p.`peopleNum`, p.`howManyTimes`, p.`whereLearnt`, p.`whereLearntOther`, p.`createdAt`, p.`customAnswer`, p.`whyParticipate`, r.`name` AS `routeName` '
			. 'FROM `'.EdkTables::PARTICIPANT_TBL.'` p '
			. 'INNER JOIN `'.EdkTables::ROUTE_TBL.'` r ON r.`id` = p.`routeId`'
			. 'WHERE p.`areaId` = :areaId '.$routeSelection.' ORDER BY p.`lastName`');
		$stmt->bindValue(':areaId', $area->getId());
		if(null !== $route) {
			$stmt->bindValue(':routeId', $route->getId());
		}
		$stmt->execute();
		$out = fopen('php://output', 'w');
		$i = 0;
		fputcsv($out, array(
			$trans->trans('FirstNameCol', [], 'edk'),
			$trans->trans('LastNameCol', [], 'edk'),
			$trans->trans('SexCol', [], 'edk'),
			$trans->trans('AgeCol', [], 'edk'),
			$trans->trans('EmailCol', [], 'edk'),
			$trans->trans('RegisteredPeopleCol', [], 'edk'),
			$trans->trans('HowManyTimesCol', [], 'edk'),
			$trans->trans('WhereLearntCol', [], 'edk'),
			$trans->trans('WhereLearntOtherCol', [], 'edk'),
			$trans->trans('CreatedAtCol', [], 'edk'),
			$trans->trans('AdditionalInformationCol', [], 'edk'),
			$trans->trans('WhyParticipateCol', [], 'edk'),
			$trans->trans('RouteCol', [], 'edk')
		));
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$row['createdAt'] = date('Y-m-d, H:i:s', $row['createdAt']);
			$row['sex'] = ($row['sex'] == 1 ? 'M' : 'F');
			$row['whereLearnt'] = WhereLearntAbout::getItem($row['whereLearnt'])->getName();
			fputcsv($out, $row);
			if(($i++) % 5 == 0) {
				fflush($out);
			}
		}
		fclose($out);
		$stmt->closeCursor();
	}
	
	/**
	 * Creates a dataset that contains information about participant numbers over time.
	 * 
	 * @param Project $project
	 * @return StatDateDataset
	 */
	public function fetchParticipantsOverTime(Project $project)
	{
		$data = $this->conn->fetchAll('SELECT * FROM `'.EdkTables::STAT_PARTICIPANT_TIME_TBL.'` WHERE `projectId` = :projectId ORDER BY `datePoint`', [':projectId' => $project->getId()]);
		$engine = new StatDateDataset(StatDateDataset::TYPE_PACKED);
		return $engine->dataset('participantNum')
			->process($data);
	}
	
	public function countWhereLearntGrouped(MembershipEntityInterface $root)
	{
		$rootQuery = '';
		if ($root instanceof Project) {
			$rootQuery = 'a.projectId';
		} elseif ($root instanceof Group) {
			$rootQuery = 'a.groupId';
		} else {
			$rootQuery = 'a.id';
		}
		
		$result = [];
		$vals = $this->conn->fetchAll('SELECT SUM(`peopleNum`) AS `participants`, `whereLearnt` AS `id` '
			. 'FROM `'.EdkTables::PARTICIPANT_TBL.'` p '
			. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.id = p.areaId '
			. 'WHERE '.$rootQuery.' = :rootId '
			. 'GROUP BY `whereLearnt`', [':rootId' => $root->getId()]);
		foreach ($vals as $val) {
			$result[$val['id']] = $val['participants'];
		}
		return $result;
	}
	
	public function countParticipantsOnRoutes(Area $area)
	{
		return $this->conn->fetchAll('SELECT s.`participantNum`, r.`name` '
			. 'FROM `'.EdkTables::REGISTRATION_SETTINGS_TBL.'` s '
			. 'INNER JOIN `'.EdkTables::ROUTE_TBL.'` r ON r.id = s.routeId '
			. 'WHERE r.areaId = :rootId '
			. 'ORDER BY s.`participantNum`', [':rootId' => $area->getId()]);
	}
	
	public function fetchBiggestAreasByParticipants(Project $project, $max)
	{
		$values = $this->conn->fetchAll('SELECT SUM(s.`participantNum`) AS `sum`, a.`id`, a.`name` '
			. 'FROM `'.EdkTables::REGISTRATION_SETTINGS_TBL.'` s '
			. 'INNER JOIN `'.EdkTables::ROUTE_TBL.'` r ON r.id = s.routeId '
			. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.id = r.areaId '
			. 'WHERE a.projectId = :rootId '
			. 'GROUP BY a.`id`, a.`name`', [':rootId' => $project->getId()]);
		usort($values, function($a, $b) {
			return $b['sum'] - $a['sum'];
		});
		$result = [];
		$i = 1;
		foreach ($values as $area) {
			$result[] = $area;
			if (++$i >= $max) {
				break;
			}
		}
		return $result;
	}
	
	public function updateStatisticsForPreviousDay($now)
	{
		$when = getdate($now - 86400);
		$upperBounds = mktime(23, 59, 59, $when['mon'], $when['mday'], $when['year']) + 1;
		$sqlDate = $when['year'].'-'.$when['mon'].'-'.$when['mday'];
		$projects = Project::fetchActiveIds($this->conn);
		foreach ($projects as $projectId) {
			$this->conn->beginTransaction();
			$total = $this->conn->fetchColumn('SELECT SUM(`peopleNum`) '
				. 'FROM `'.EdkTables::PARTICIPANT_TBL.'` p '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.id = p.areaId '
				. 'WHERE p.`createdAt` < :upperTime AND a.projectId = :projectId', [':upperTime' => $upperBounds, ':projectId' => $projectId]);
			$this->updateStatsFor($projectId, $sqlDate, $total);
			$this->conn->commit();
		}
	}
	
	public function updateStatisticsForAllDays()
	{
		$projects = Project::fetchActiveIds($this->conn);
		foreach ($projects as $projectId) {
			$participants = $this->conn->fetchAll('SELECT p.`peopleNum`, p.`createdAt` '
				. 'FROM `'.EdkTables::PARTICIPANT_TBL.'` p '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.id = p.areaId '
				. 'WHERE a.projectId = :projectId ORDER BY p.`createdAt`', [':projectId' => $projectId]);
			$total = 0;
			$lastDay = null;
			$first = true;
			foreach ($participants as $participant) {
				$when = getdate($participant['createdAt']);
				$currentDay = $when['year'].'-'.$when['mon'].'-'.$when['mday'];
				if (!$first && $lastDay != $currentDay) {
					$this->updateStatsFor($projectId, $lastDay, $total);
					$lastDay = $currentDay;
				}
				$total += $participant['peopleNum'];
				if ($first) {
					$first = false;
					$lastDay = $currentDay;
				}
			}
		}
	}
	
	private function updateStatsFor($projectId, $datePoint, $total)
	{
		if (!empty($total)) {
			$this->conn->executeQuery('INSERT INTO `'.EdkTables::STAT_PARTICIPANT_TIME_TBL.'` (`projectId`, `datePoint`, `participantNum`)'
				. 'VALUES(:projectId, :datePoint, :pn1) ON DUPLICATE KEY UPDATE `participantNum` = :pn2', [
					':projectId' => $projectId,
					':datePoint' => $datePoint,
					':pn1' => $total,
					':pn2' => $total
				]);
		}
	}
}
