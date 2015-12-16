<?php
namespace Cantiga\CoreBundle\Statistics;

use Cantiga\CoreBundle\Repository\CoreStatisticsRepository;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\StatsInterface;
use Cantiga\Metamodel\Statistics\ChartJSDateDatasetRenderer;
use Cantiga\Metamodel\Statistics\StatDateDataset;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Tomasz Jędrzejewski
 */
class AreaRequestsOverTimeStats implements StatsInterface
{
	/**
	 * @var Connection
	 */
	private $conn;
	/**
	 * @var CoreStatisticsRepository
	 */
	private $repo;
	/**
	 * @var TranslatorInterface
	 */
	private $translator;
	/**
	 * @var StatDateDataset
	 */
	private $data;
	
	public function __construct(Connection $conn, CoreStatisticsRepository $repository, TranslatorInterface $translator)
	{
		$this->conn = $conn;
		$this->repo = $repository;
		$this->translator = $translator;
	}

	public function collectData(IdentifiableInterface $root)
	{
		$this->data = $this->repo->fetchAreaRequestTimeData($root);
		return true;
	}

	public function getTitle()
	{
		return 'Area requests over time';
	}

	public function renderPlaceholder(TwigEngine $tpl)
	{
		return $tpl->render('CantigaCoreBundle:Stats:area-requests-over-time.html.twig');
	}

	public function renderStatistics(TwigEngine $tpl)
	{
		$renderer = new ChartJSDateDatasetRenderer();
		$renderer->data($this->translator->trans('New', [], 'statuses'), '210,214,222')
			->data($this->translator->trans('Verification', [], 'statuses'), '60,141,188')
			->data($this->translator->trans('Approved', [], 'statuses'), '0,166,90')
			->data($this->translator->trans('Rejected', [], 'statuses'), '221,75,57');
		return $tpl->render('CantigaCoreBundle:Stats:area-requests-over-time.js.twig', array(
			'data' => $renderer->generateData($this->data)
		));
	}

}
