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
namespace WIO\EdkBundle\Repository;

use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\FileRepositoryInterface;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\Metamodel\Transaction;
use Cantiga\MilestoneBundle\Entity\NewMilestoneStatus;
use Cantiga\MilestoneBundle\Event\ActivationEvent;
use Cantiga\MilestoneBundle\MilestoneEvents;
use Doctrine\DBAL\Connection;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use WIO\EdkBundle\EdkTables;
use WIO\EdkBundle\Entity\EdkRoute;
use PDO;

class EdkRouteRepository
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
	 * @var EventDispatcherInterface
	 */
	private $eventDispatcher;
	/**
	 * @var TimeFormatterInterface
	 */
	private $timeFormatter;
	/**
	 * @var FileRepositoryInterface
	 */
	private $fileRepository;
	/**
	 * @var Project|Group|Area
	 */
	private $root;
	
	public function __construct(Connection $conn, Transaction $transaction, EventDispatcherInterface $eventDispatcher, TimeFormatterInterface $timeFormatter, FileRepositoryInterface $fileRepository)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->eventDispatcher = $eventDispatcher;
		$this->timeFormatter = $timeFormatter;
		$this->fileRepository = $fileRepository;
	}
	
	public function setRootEntity(HierarchicalInterface $root)
	{
		$this->root = $root;
	}
	
	/**
	 * @return DataTable
	 */
	public function createDataTable()
	{
		$dt = new DataTable();
		$dt->id('id', 'i.id');
		
		if (!($this->root instanceof Area)) {
			$dt->searchableColumn('areaName', 'a.name');
		}
		$dt
			->searchableColumn('name', 'i.name')
			->searchableColumn('routeFrom', 'i.routeFrom')
			->searchableColumn('routeTo', 'i.routeTo')
			->column('routeLength', 'i.routeLength')
			->column('updatedAt', 'i.updatedAt')
			->column('approved', 'i.approved')
			->column('commentNum', 'i.commentNum');
		return $dt;
	}
	
	public function listData(DataTable $dataTable)
	{
		$qb = QueryBuilder::select()
			->field('i.id', 'id');
		
		if (!($this->root instanceof Area)) {
			$qb->field('a.id', 'areaId');
			$qb->field('a.name', 'areaName');
			$qb->join(CoreTables::AREA_TBL, 'a', QueryClause::clause('a.id = i.areaId'));
		}
		$qb
			->field('i.name', 'name')
			->field('i.routeFrom', 'routeFrom')
			->field('i.routeTo', 'routeTo')
			->field('i.routeLength', 'routeLength')
			->field('i.updatedAt', 'updatedAt')
			->field('i.approved', 'approved')
			->field('i.commentNum', 'commentNum')
			->from(EdkTables::ROUTE_TBL, 'i');
		
		if ($this->root instanceof Area) {
			$qb->where(QueryClause::clause('i.`areaId` = :areaId', ':areaId', $this->root->getId()));
		} elseif ($this->root instanceof Group) {
			$qb->where(QueryClause::clause('a.`groupId` = :groupId', ':groupId', $this->root->getId()));
		} elseif ($this->root instanceof Project) {
			$qb->where(QueryClause::clause('a.`projectId` = :projectId', ':projectId', $this->root->getId()));
		}
			

		$qb->postprocess(function($row) {
			$row['routeLength'] .= ' km';
			$row['updatedAtText'] = $this->timeFormatter->ago($row['updatedAt']);
			$row['removable'] = !$row['approved'];
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
	
	public function findRouteSummary(Area $area)
	{
		return $this->conn->fetchAll('SELECT `id`, `name`, `approved` FROM `'.EdkTables::ROUTE_TBL.'` WHERE `areaId` = :id', [':id' => $area->getId()]);
	}
	
	public function getItem($id): EdkRoute
	{
		$this->transaction->requestTransaction();
		try {
			$item = EdkRoute::fetchByRoot($this->conn, $id, $this->root);
			if(false === $item) {
				$this->transaction->requestRollback();
				throw new ItemNotFoundException('The specified item has not been found.', $id);
			}
			return $item;
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function getComments(EdkRoute $item): array
	{
		$this->transaction->requestTransaction();
		try {
			return [
				'status' => 1,
				'messageNum' => $item->getCommentNum(),
				'messages' => $item->getComments($this->conn, $this->timeFormatter)
			];
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function getItemBySlug($slug): EdkRoute
	{
		$this->transaction->requestTransaction();
		try {
			$item = EdkRoute::fetchBySlug($this->conn, $slug);
			if(false === $item) {
				$this->transaction->requestRollback();
				throw new ItemNotFoundException('The specified item has not been found.', $id);
			}
			return $item;
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	/**
	 * Fetches minimal set of information required to download a file.
	 * 
	 * @return array
	 */
	public function getFileDownloadInformation($slug, $requestedFile, $requestedSetting)
	{
		$this->transaction->requestTransaction();
		try {
			$data = $this->conn->fetchAssoc('SELECT r.id, r.publicAccessSlug, r.'.$requestedFile.' AS `file`, s.value AS `setting` '
				. 'FROM `'.EdkTables::ROUTE_TBL.'` r '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.id = r.areaId '
				. 'INNER JOIN `'.CoreTables::PROJECT_SETTINGS_TBL.'` s ON s.`projectId` = a.`projectId` '
				. 'WHERE r.`publicAccessSlug` = :slug AND s.`key` = :key', [':slug' => $slug, ':key' => $requestedSetting]);
			if(false === $data) {
				$this->transaction->requestRollback();
				throw new ItemNotFoundException('The specified item has not been found.', $id);
			}
			return $data;
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function insert(EdkRoute $item)
	{
		$this->transaction->requestTransaction();
		try {
			if ($this->root instanceof Area) {
				$item->setArea($this->root);
			}
			$item->storeFiles($this->fileRepository);
			return $item->insert($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function update(EdkRoute $item)
	{
		$this->transaction->requestTransaction();
		try {
			$item->updateFiles($this->fileRepository);
			return $item->update($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function remove(EdkRoute $item)
	{
		$this->transaction->requestTransaction();
		try {
			$item->cleanupFiles($this->fileRepository);
			return $item->remove($this->conn);
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function getRecentCommentActivity($count)
	{
		$this->transaction->requestTransaction();
		try {
			$items = $this->conn->fetchAll('SELECT r.`id` AS `routeId`, r.name AS `routeName`, a.`name` AS `areaName`, u.`name` AS `username`, u.`avatar`, c.`createdAt`, c.`message` '
				. 'FROM `'.EdkTables::ROUTE_COMMENT_TBL.'` c '
				. 'INNER JOIN `'.EdkTables::ROUTE_TBL.'` r ON r.`id` = c.`routeId` '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON r.`areaId` = a.`id` '
				. 'INNER JOIN `'.CoreTables::USER_TBL.'` u ON u.`id` = c.`userId` '
				. $this->createWhereClause()
				. 'ORDER BY c.`createdAt` DESC LIMIT '.$count, [':itemId' => $this->root->getId()]);
			foreach ($items as &$item) {
				if (strlen($item['message']) > 60) {
					$item['truncatedContent'] = substr($item['message'], 0, 60);
					if (ord($item['truncatedContent']{59}) > 127) {
						$item['truncatedContent'] = substr($item['message'], 0, 59);
					}
					$item['truncatedContent'] .= '...';
				} else {
					$item['truncatedContent'] = $item['message'];
				}
			}
			return $items;
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function getRecentlyChangedRoutes($count)
	{
		$this->transaction->requestTransaction();
		try {
			$items = $this->conn->fetchAll('SELECT r.`id`, r.`name`, a.`name` AS `areaName`, r.`updatedAt` '
				. 'FROM `'.EdkTables::ROUTE_TBL.'` r '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON r.`areaId` = a.`id` '
				. $this->createWhereClause()
				. 'ORDER BY r.`updatedAt` DESC LIMIT '.$count, [':itemId' => $this->root->getId()]);
			foreach ($items as &$item) {
				$item['updatedAt'] = $this->timeFormatter->ago($item['updatedAt']);
			}
			return $items;
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function countRoutes()
	{
		$this->transaction->requestTransaction();
		try {
			return $this->conn->fetchColumn('SELECT COUNT(r.`id`) '
				. 'FROM `'.EdkTables::ROUTE_TBL.'` r '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON r.`areaId` = a.`id` '
				. $this->createWhereClause(), [':itemId' => $this->root->getId()]);
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function approve(EdkRoute $item)
	{
		$this->transaction->requestTransaction();
		try {
			if(!$item->approve($this->conn)) {
				throw new ModelException('Cannot approve this this route.');
			}
			$this->eventDispatcher->dispatch(MilestoneEvents::ACTIVATION_EVENT, new ActivationEvent(
				$item->getArea()->getProject(),
				$item->getArea()->getPlace(),
				'route.approved',
				$this->getActivationFunc($item)
			));
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function revoke(EdkRoute $item)
	{
		$this->transaction->requestTransaction();
		try {
			if(!$item->revoke($this->conn)) {
				throw new ModelException('Cannot revoke this this route.');
			}
			$this->eventDispatcher->dispatch(MilestoneEvents::ACTIVATION_EVENT, new ActivationEvent(
				$item->getArea()->getProject(),
				$item->getArea()->getPlace(),
				'route.approved',
				$this->getActivationFunc($item)
			));
		} catch (Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	public function countRouteFiles(Project $project)
	{
		$data = $this->conn->fetchAll('SELECT r.`descriptionFile`, r.`mapFile` '
			. 'FROM `'.EdkTables::ROUTE_TBL.'` r '
			. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.`id` = r.`areaId` '
			. 'WHERE a.projectId = :rootId AND r.`approved` = 1', [':rootId' => $project->getId()]);
		
		$types = [
			0 => ['name' => 'FileStatOnlyGPS', 'value' => 0],
			1 => ['name' => 'FileStatMapPresent', 'value' => 0],
			2 => ['name' => 'FileStatGuidePresent', 'value' => 0],
			3 => ['name' => 'FileStatMapGuidePresent', 'value' => 0],
		];
		foreach ($data as $route) {
			if (!empty($route['mapFile']) && !empty($route['descriptionFile'])) {
				$types[3]['value']++;
			} elseif (!empty($route['mapFile'])) {
				$types[1]['value']++;
			} elseif (!empty($route['descriptionFile'])) {
				$types[2]['value']++;
			} else {
				$types[0]['value']++;
			}
		}
		return $types;
	}
	
	public function importFrom(HierarchicalInterface $source, HierarchicalInterface $destination)
	{
		$this->transaction->requestTransaction();
		try {
			$sourceRoutes = $this->conn->fetchAll('SELECT * FROM `'.EdkTables::ROUTE_TBL.'` WHERE `areaId` = :sourceAreaId FOR UPDATE', [':sourceAreaId' => $source->getId()]);
			$sourceNotes = $this->findAllEditableNotes($source->getId());
			$destinationRoutes = $this->conn->fetchAll('SELECT `importedFrom` FROM `'.EdkTables::ROUTE_TBL.'` WHERE `areaId` = :dstAreaId FOR UPDATE', [':dstAreaId' => $destination->getId()]);
			$set = [];
			foreach ($destinationRoutes as $row) {
				$set[$row['importedFrom']] = true;
			}
			foreach ($sourceRoutes as $route) {
				if (!isset($set[$route['id']])) {
					$item = new EdkRoute();
					$item->setArea($destination);
					$item->setName($route['name']);
					$item->setRouteFrom($route['routeFrom']);
					$item->setRouteTo($route['routeTo']);
					$item->setRouteCourse($route['routeCourse']);
					$item->setRouteLength($route['routeLength']);
					$item->setRouteAscent($route['routeAscent']);
					$item->setRouteObstacles($route['routeObstacles']);
					$item->setRouteType($route['routeType']);
					
					if (!empty($route['gpsTrackFile'])) {
						$item->setGpsTrackFile($this->fileRepository->duplicateFile($route['gpsTrackFile']));
					}
					if (!empty($route['mapFile'])) {
						$item->setMapFile($this->fileRepository->duplicateFile($route['mapFile']));
					}
					if (!empty($route['descriptionFile'])) {
						$item->setDescriptionFile($this->fileRepository->duplicateFile($route['descriptionFile']));
					}
					
					$item->setImportedFrom($route['id']);
					$item->insert($this->conn);
					
					foreach (EdkRoute::getNoteTypes() as $type => $name) {
						if (isset($sourceNotes[$route['id']][$type])) {
							$item->saveEditableNote($this->conn, $type, $sourceNotes[$route['id']][$type]);
						}
					}
				}
			}
		} catch(Exception $ex) {
			$this->transaction->requestRollback();
			throw $ex;
		}
	}
	
	private function findAllEditableNotes($areaId)
	{
		$stmt = $this->conn->prepare('SELECT * FROM `'.EdkTables::ROUTE_NOTE_TBL.'` n INNER JOIN `'.EdkTables::ROUTE_TBL.'` r ON r.`id` = n.`routeId` WHERE r.`areaId` = :areaId');
		$stmt->bindValue(':areaId', $areaId);
		$stmt->execute();
		
		$results = [];
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if (!isset($results[$row['routeId']])) {
				$results[$row['routeId']] = [];
			}
			$results[$row['routeId']][$row['noteType']] = $row['content'];
		}
		$stmt->closeCursor();
		return $results;
	}
	
	private function getActivationFunc(EdkRoute $route)
	{
		$conn = $this->conn;
		return function() use($conn, $route) {
			$count = $conn->fetchColumn('SELECT COUNT(`id`) FROM `'.EdkTables::ROUTE_TBL.'` WHERE `approved` = 1 AND `routeType` = '.EdkRoute::TYPE_FULL.' AND `areaId` = :areaId', [':areaId' => $route->getArea()->getId()]);
			return NewMilestoneStatus::create($count > 0);
		};
	}
	
	private function createWhereClause()
	{
		if ($this->root instanceof Area) {
			return 'WHERE r.`areaId` = :itemId ';				
		} elseif ($this->root instanceof Group) {
			return 'WHERE a.`groupId` = :itemId ';
		} elseif ($this->root instanceof Project) {
			return 'WHERE a.`projectId` = :itemId ';
		}
	}
}
