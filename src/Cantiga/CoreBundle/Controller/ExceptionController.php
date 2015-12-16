<?php
namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Exception\MailException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\TwigBundle\Controller\ExceptionController as BaseExceptionController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

/**
 * @author Tomasz Jędrzejewski
 */
class ExceptionController extends BaseExceptionController
{	
    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null)
    {
		if ($exception->getClass() == MailException::class) {
			return new Response($this->twig->render('CantigaCoreBundle:Exception:mail-exception.html.twig',
				array('message' => $exception->getMessage())
			), 501);
		}		
		return parent::showAction($request, $exception, $logger);
	}
}
