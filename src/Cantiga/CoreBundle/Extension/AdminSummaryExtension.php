<?php
namespace Cantiga\CoreBundle\Extension;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Repository\CoreStatisticsRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Displays a short numerical summary of the system on the admin dashboard.
 *
 * @author Tomasz Jędrzejewski
 */
class AdminSummaryExtension implements DashboardExtensionInterface
{
	/**
	 * @var CoreStatisticsRepository 
	 */
	private $repository;
	
	public function __construct(CoreStatisticsRepository $repository)
	{
		$this->repository = $repository;
	}
	
	public function getPriority()
	{
		return self::PRIORITY_HIGH;
	}

	public function render(CantigaController $controller, Request $request, Workspace $workspace, Project $project = null)
	{
		return $controller->renderView('CantigaCoreBundle:Admin:admin-summary.html.twig', ['data' => $this->repository->fetchAdminSummary()]);
	}
}
