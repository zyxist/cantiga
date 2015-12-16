<?php
namespace Cantiga\CoreBundle\Api\Controller;

use Cantiga\CoreBundle\Api\ExtensionPoints\ExtensionPointFilter;
use Cantiga\CoreBundle\Api\Workspace\ProjectWorkspace;
use Cantiga\CoreBundle\Api\WorkspaceAwareInterface;
use Cantiga\CoreBundle\Entity\Project;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


/**
 * This class shall be extended by all the controllers that work in the
 * project workspace.
 *
 * @author Tomasz Jędrzejewski
 */
class ProjectPageController extends CantigaController implements WorkspaceAwareInterface, ProjectAwareControllerInterface
{
	/**
	 * @var ProjectWorkspace
	 */
	private $workspace;
	/**
	 * @var ExtensionPointsFilter
	 */
	private $extensionFilter;
	/**
	 * @var TokenStorageInterface
	 */
	private $tokenStorage;
	
	public function createWorkspace()
	{
		$this->tokenStorage = $this->get('security.token_storage');
		return $this->workspace = new ProjectWorkspace($this->get('cantiga.core.membership.project'));
	}
	
	/**
	 * @return ProjectWorkspace
	 */
	public function getWorkspace()
	{
		return $this->workspace;
	}
	
	public function getSlug()
	{
		return $this->tokenStorage->getToken()->getMembershipEntity()->getSlug();
	}
	
	/**
	 * @return Project
	 */
	public function getActiveProject()
	{
		return $this->tokenStorage->getToken()->getMembershipEntity();
	}
	
	/**
	 * @return ExtensionPointFilter
	 */
	public function getExtensionPointFilter()
	{
		if (null === $this->extensionFilter) {
			$this->extensionFilter = $this->get('security.token_storage')->getToken()->getMasterProject()->createExtensionPointFilter();
		}
		return new ExtensionPointFilter();
	}
	
	/**
	 * Shortcut for instantiating an extension point from the project settings.
	 * 
	 * @param string $extensionPoint Name of the extension point
	 * @param string $setting Name of the project setting which contains the name of the selected implementation
	 * @return object
	 */
	public function extensionPointFromSettings($extensionPoint, $setting)
	{
		return $this->getExtensionPoints()
			->getImplementation($extensionPoint, 
				$this->getExtensionPointFilter()->fromSettings($this->get('cantiga.project.settings'), $setting)
			);
	}
}
