<?php
namespace Cantiga\CoreBundle\Extension;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Repository\AppTextRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Displays a custom static text on a dashboard, if such a text is defined in the currently selected language.
 *
 * @author Tomasz Jędrzejewski
 */
class DashboardTextExtension implements DashboardExtensionInterface
{
	/**
	 * @var AppTextRepository 
	 */
	private $textRepository;
	
	public function __construct(AppTextRepository $repository)
	{
		$this->textRepository = $repository;
	}
	
	public function getPriority()
	{
		return self::PRIORITY_HIGH + 5;
	}

	public function render(CantigaController $controller, Request $request, Workspace $workspace, Project $project = null)
	{
		$text = $this->textRepository->getTextOrFalse('cantiga:dashboard:'.$workspace->getKey(), $request, $project);
		if (false === $text) {
			return '';
		}
		return $controller->renderView('CantigaCoreBundle:AppText:dashboard-element.html.twig', ['text' => $text]);
	}
}
