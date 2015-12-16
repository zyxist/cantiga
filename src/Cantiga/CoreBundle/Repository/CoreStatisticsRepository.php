<?php
namespace Cantiga\CoreBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\AreaRequest;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Event\AreaRequestEvent;
use Cantiga\Metamodel\Statistics\StatDateDataset;
use Doctrine\DBAL\Connection;

/**
 * Provides a lot of statistical data for the core bundle model.
 *
 * @author Tomasz Jędrzejewski
 */
class CoreStatisticsRepository
{
	/**
	 * @var Connection
	 */
	private $conn;
	
	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}
	
	/**
	 * Creates a dataset that contains information about area request status in the given project over
	 * time.
	 * 
	 * @param Project $project
	 * @return StatDateDataset
	 */
	public function fetchAreaRequestTimeData(Project $project)
	{
		$data = $this->conn->fetchAll('SELECT * FROM `'.CoreTables::STAT_ARQ_TIME_TBL.'` WHERE `projectId` = :projectId ORDER BY `datePoint`', [':projectId' => $project->getId()]);
		$engine = new StatDateDataset(StatDateDataset::TYPE_PACKED);
		return $engine->dataset('requestsNew')
			->dataset('requestsVerification')
			->dataset('requestsApproved')
			->dataset('requestsRejected')
			->process($data);
	}
	
	public function fetchAdminSummary()
	{
		return [
			'userNum' => $this->conn->fetchColumn('SELECT COUNT(`id`) FROM `'.CoreTables::USER_TBL.'` WHERE `removed` = 0'),
			'userRegistrationNum' => $this->conn->fetchColumn('SELECT COUNT(`id`) FROM `'.CoreTables::USER_REGISTRATION_TBL.'`'),
			'invitationNum' => $this->conn->fetchColumn('SELECT COUNT(`id`) FROM `'.CoreTables::INVITATION_TBL.'`'),
			'projectNum' => $this->conn->fetchColumn('SELECT COUNT(`id`) FROM `'.CoreTables::PROJECT_TBL.'`'),
		];
	}
	
	public function fetchProjectSummary(Project $project)
	{
		return [
			'areaRequestNum' => $this->conn->fetchColumn('SELECT COUNT(`id`) FROM `'.CoreTables::AREA_REQUEST_TBL.'` WHERE `projectId` = :projectId', [':projectId' => $project->getId()]),
			'areaNum' => $this->conn->fetchColumn('SELECT COUNT(`id`) FROM `'.CoreTables::AREA_TBL.'` WHERE `projectId` = :projectId', [':projectId' => $project->getId()]),
			'groupNum' => $this->conn->fetchColumn('SELECT COUNT(`id`) FROM `'.CoreTables::GROUP_TBL.'` WHERE `projectId` = :projectId', [':projectId' => $project->getId()]),
		];
	}
	
	/**
	 * Updates the statistics for area requests in the current day.
	 * 
	 * @param \Cantiga\CoreBundle\Repository\AreaRequestEvent $event
	 */
	public function onAreaRequestStatusChange(AreaRequestEvent $event)
	{
		$project = $event->getAreaRequest()->getProject();
		$values = [
			0 => 0,
			1 => 0,
			2 => 0,
			3 => 0
		];
		$calculated = $this->conn->fetchAll('SELECT `status`, COUNT(`id`) AS `counted` FROM `'.CoreTables::AREA_REQUEST_TBL.'` WHERE `projectId` = :projectId GROUP BY `status`', [':projectId' => $project->getId()]);
		foreach ($calculated as $row) {
			$values[$row['status']] = $row['counted'];
		}
		$date = date('Y-m-d');
		$this->conn->executeQuery('INSERT INTO `'.CoreTables::STAT_ARQ_TIME_TBL.'` (`projectId`, `datePoint`, `requestsNew`, `requestsVerification`, `requestsApproved`, `requestsRejected`)'
			. 'VALUES(:projectId, :datePoint, :rn1, :rv1, :ra1, :rr1) ON DUPLICATE KEY UPDATE `requestsNew` = :rn2, `requestsVerification` = :rv2, `requestsApproved` = :ra2, `requestsRejected` = :rr2', [
				':projectId' => $project->getId(),
				':datePoint' => $date,
				':rn1' => $values[AreaRequest::STATUS_NEW],
				':rv1' => $values[AreaRequest::STATUS_VERIFICATION],
				':ra1' => $values[AreaRequest::STATUS_APPROVED],
				':rr1' => $values[AreaRequest::STATUS_REVOKED],
				':rn2' => $values[AreaRequest::STATUS_NEW],
				':rv2' => $values[AreaRequest::STATUS_VERIFICATION],
				':ra2' => $values[AreaRequest::STATUS_APPROVED],
				':rr2' => $values[AreaRequest::STATUS_REVOKED],
			]);
	}
}
