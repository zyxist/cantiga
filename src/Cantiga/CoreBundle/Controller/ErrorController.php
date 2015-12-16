<?php
namespace Cantiga\CoreBundle\Controller;
use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles more critical errors that must get a dedicated page. The <tt>ErrorListener</tt> redirects
 * here, whenever such an error is captured.
 * 
 * @author Tomasz Jędrzejewski
 */
class ErrorController extends CantigaController
{
	/**
	 * @Route("/error/areas-not-supported", name="cantiga_error_areas_not_supported")
	 */
	public function areasNotSupportedAction()
	{
		return new Response($this->renderView('CantigaCoreBundle:Exception:areas-not-supported.html.twig'), 403);
	}
}
