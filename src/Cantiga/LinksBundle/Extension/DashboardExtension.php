<?php
namespace Cantiga\LinksBundle\Extension;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Extension\DashboardExtensionInterface;
use Cantiga\LinksBundle\Entity\Link;
use Cantiga\LinksBundle\Repository\LinkRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Displays the links on the given dashboard.
 *
 * @author Tomasz Jędrzejewski
 */
class DashboardExtension implements DashboardExtensionInterface
{
	private $linkRepository;
	
	public function __construct(LinkRepository $repository)
	{
		$this->linkRepository = $repository;
	}
	
	public function getPriority()
	{
		return self::PRIORITY_HIGH;
	}

	public function render(CantigaController $controller, Request $request, Workspace $workspace, Project $project = null)
	{
		if (null !== $project) {
			$this->linkRepository->setProject($project);
		}
		$presentationPlace = null;
		if ($workspace instanceof Workspace\UserWorkspace) {
			$presentationPlace = Link::PRESENT_USER;
		} elseif ($workspace instanceof Workspace\AdminWorkspace) {
			$presentationPlace = Link::PRESENT_ADMIN;
		} elseif ($workspace instanceof Workspace\AreaWorkspace) {
			$presentationPlace = Link::PRESENT_AREA;
		} elseif ($workspace instanceof Workspace\GroupWorkspace) {
			$presentationPlace = Link::PRESENT_GROUP;
		} elseif ($workspace instanceof Workspace\ProjectWorkspace) {
			$presentationPlace = Link::PRESENT_PROJECT;
		}
		if (null === $presentationPlace) {
			return '';
		}
		return $controller->renderView('CantigaLinksBundle:Links:dashboard-element.html.twig', array('links' => $this->linkRepository->findLinks($presentationPlace)));
	}
}
