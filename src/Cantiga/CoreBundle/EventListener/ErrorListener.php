<?php
namespace Cantiga\CoreBundle\EventListener;

use Cantiga\CoreBundle\Exception\AreasNotSupportedException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Http\HttpUtils;
/**
 * Description of ErrorListener
 *
 * @author Tomasz Jędrzejewski
 */
class ErrorListener
{
	private $httpUtils;
	
	public function __construct(HttpUtils $httpUtils)
	{
		$this->httpUtils = $httpUtils;
	}
	
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
		if ($event->getException() instanceof AreasNotSupportedException) {
            $subRequest = $this->httpUtils->createRequest($event->getRequest(), 'cantiga_error_areas_not_supported');
            $event->setResponse($event->getKernel()->handle($subRequest, HttpKernelInterface::SUB_REQUEST, true));
		}
	}
}
